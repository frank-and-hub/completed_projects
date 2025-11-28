const mongoose = require('mongoose');

// models
const User = require('../models/user');
const Role = require('../models/role');
const File = require('../models/file');
const SocialDetail = require('../models/social_detail');
const UserPermission = require('../models/user_permission');

// helper function
const helper = require('../utils/helper');

// services
const AuthServices = require('../services/AuthServices');

// config url
const url = require('../config/url');;
const { getDataById, insertNewData } = require('../services/UserServices');

// base url
const baseurl = `${url.apiUrl}`;
const apiBaseUrl = `${url.apiBaseUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const unauthorized = `${process.env.UNAUTHORIZED}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
// stsic base
const constName = 'users/';

exports.index = async (req, res, next) => {
    try {
        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
        const { role_id, status, gender, search } = req.query;

        if (role_id) filter.role = role_id;
        if (status) filter.status = status;
        if (gender) filter.gender = gender;

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                {
                    $or: [
                        { "name.first_name": { $regex: trimmedSearch, $options: "i" } },
                        { "name.middle_name": { $regex: trimmedSearch, $options: "i" } },
                        { "name.last_name": { $regex: trimmedSearch, $options: "i" } }
                    ]
                },
                { slug: { $regex: trimmedSearch, $options: "i" } },
                { email: { $regex: trimmedSearch, $options: "i" } },
                { $expr: { $regexMatch: { input: { $toString: "$phone" }, regex: trimmedSearch } } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await User.countDocuments({
            ...filter
        });

        const query = User.find(filter)
            .select('_id name email dial_code phone gender image status role about updated_by slug')
            .populate('role', '_id name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name')
            .populate('image', '_id name path');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const users = await query;

        if (users.length === 0) return res.status(200).json({ message: `Users not found!`, data: [] });

        const userPromises = users.map(async (user) => {
            const { _id, full_name, email, dial_code, phone, role, image, status, slug} = user;
            return {
                'id': _id,
                'name': full_name,
                'email': email,
                'dial_code': dial_code,
                'phone': phone,
                'role': role,
                'image': image,
                'status': status,
                'slug': slug
            }
        });

        const userResponses = await Promise.all(userPromises);
        res.status(200).json({
            message: `Users list retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: userResponses
            }, title: 'User'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create user form`,
            body: {
                'name': {
                    'first_name': 'String',
                    'middle_name': 'String',
                    'last_name': 'String'
                },
                'email': 'String',
                'dial_code': 'String',
                'phone': 'Digits',
                'password': 'String',
                'role_id': 'SchemaId',
            },
            title: 'Add user'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { email, password, role_id } = req.body;
    try {
        let userId = req?.userData?.id;

        const existsUser = await User.findOne({ email: email, status: status_active });
        if (existsUser) return res.status(409).json({ message: 'User already exists' });

        const roleData = await Role.findById(role_id).select('_id').where('status').equals(status_active);
        if (!roleData) return res.status(401).json({ message: unauthorized });

        const newData = await insertNewData(req.body, userId);

        const response = {
            'id': newData?._id,
            'name': {
                'first_name': newData?.name?.first_name,
                'middle_name': newData?.name?.middle_name,
                'last_name': newData?.name?.last_name
            },
            'email': newData?.email,
            'dial_code': newData?.dial_code,
            'phone': newData?.phone,
            'role': newData?.role,
            'password': password,
        }
        return res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const userData = await getDataById(id, res);

        const social_details = await SocialDetail.find({ 'user': userData?._id })
            .select('_id name url icon')
            .sort({ _id: -1 });

        const userWithDetails = {
            ...userData.toObject(),
            social_details,
        };
        res.status(200).json({ message: `User founded`, data: userWithDetails, title: `View ${userData?.name} user detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const userData = await getDataById(id, res);
        const { _id, name, email, dial_code, phone, password_text, role } = userData;
        const userResponses = {
            '_id': _id,
            'name': {
                'first_name': name?.first_name,
                'middle_name': name?.middle_name,
                'last_name': name?.last_name
            },
            'email': email,
            'dial_code': dial_code,
            'phone': phone,
            'password': password_text,
            'role_id': role?._id,
        }
        res.status(200).json({ message: `User details founded`, data: userResponses, title: `Edit ${userData?.name} user detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const user = await this.findData(id, res);
        if (!user) return res.status(404).json({ message: `User not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (user may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        if (updateOps?.role_id) {
            updateOps['role'] = updateOps.role_id;
        }

        if (updateOps?.password) {
            updateOps['password_text'] = updateOps.password;
            updateOps['password'] = await AuthServices.hashPassword(updateOps.password);
        }

        if (updateOps?.name) {
            updateOps['name']['first_name'] = updateOps.first_name;
            updateOps['name']['middle_name'] = updateOps.middle_name;
            updateOps['name']['last_name'] = updateOps.last_name;
        }

        if (typeof updateOps?.phone === 'string') {
            updateOps['phone'] = updateOps.phone.replace('-', '').replace('-', '').replace(' ', '');
        }

        const result = await User.updateOne({ _id: user._id }, { $set: updateOps });

        if (result.modifiedCount > 0) {
            const updatedUser = await getDataById(id, res);
            const { _id, name, email, dial_code, phone, gender, role, image, about } = updatedUser;
            const userData = {
                'id': _id,
                'name': {
                    'first_name': name?.first_name,
                    'middle_name': name?.middle_name,
                    'last_name': name?.last_name
                },
                'email': email,
                'dial_code': dial_code,
                'phone': phone,
                'gender': gender,
                'role': role,
                'image': image,
                'about': about,
            }
            return res.status(200).json({ message: `User details updated successfully`, data: userData });
        }

        res.status(404).json({ message: `User not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getUser = await User.findById(id).select('_id').where('status').equals(!status_active);
        if (!getUser) return res.status(404).json({ message: 'User not found' });

        // const userData = await User.deleteOne({ _id: id });
        // if (userData.deletedCount === 1) {

        const userData = await User.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (userData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': {
                        'first_name': 'String',
                        'middle_name': 'String',
                        'last_name': 'String'
                    },
                    'email': 'string',
                    'password': 'string',
                    'role': 'ID',
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Not found` });
    } catch (err) { next(err) }
}

exports.profile = async (req, res, next) => {
    const { id } = req.params;
    const file = req.file;
    try {
        if (!file) return res.status(404).json({ message: `File not found` });

        const getUserData = await this.findData(id, res);
        if (!getUserData) return res.status(404).json({ message: `User not found` });

        const newFile = new File({
            _id: new mongoose.Types.ObjectId(),
            name: file.filename,
            path: `${apiBaseUrl}/file/${file.filename}`,
            slug: helper.makeSlug(file.filename)
        });

        const newData = await newFile.save();
        const result = await User.updateOne({ _id: getUserData._id }, { $set: { image: newData._id } });

        const response = {
            'id': newData._id,
            'name': newData.name,
            'path': newData.path,
        };

        if (result.modifiedCount > 0) return res.status(200).json({ message: `User image updated`, data: response });

        res.status(404).json({ message: `User not found or no image to update`, data: [] });
    } catch (err) { next(err) }
}

exports.assignRole = async (req, res, next) => {
    const { id } = req.params;
    const roleId = req.body.role_id;
    try {

        const [roleData, userData] = await Promise.all([
            Role.findById(roleId).select('_id').where('status').equals(status_active),
            this.findData(id, res)
        ]);

        if (!roleData) return res.status(404).json({ message: `Role not found` });

        if (!userData) return res.status(404).json({ message: `User not found` });

        const updateResult = await User.updateOne({ _id: userData._id }, { $set: { role: roleId } });

        if (updateResult.modifiedCount > 0) {
            const updatedUser = await User.findById(id).select('_id name email phone gender').where('status').equals(status_active);
            const { _id, name, email, phone, gender } = updatedUser;
            const response = {
                'id': _id,
                'name': {
                    'first_name': name?.first_name,
                    'middle_name': name?.middle_name,
                    'last_name': name?.last_name
                },
                'email': email,
                'phone': phone,
                'gender': gender,
                'role': roleData?.name,
            }
            return res.status(200).json({ message: `User role updated`, data: response });
        }
        res.status(404).json({ message: `No details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.verifyPassword = async (req, res, next) => {
    try {
        const { id, password } = req.body;
        const user = await User.findById(id).select('_id password').where('status').equals(status_active);
        if (!user) return res.status(404).json({ message: `User not found!`, });

        const isPasswordValid = await AuthServices.comparePasswords(password, user.password);
        if (!isPasswordValid) return res.status(200).json({ message: `Incorrect current password`, data: false });

        return res.status(200).json({ message: `Auth user password is correct 👍`, data: true });
    } catch (err) { next(err) }
}

exports.changePassword = async (req, res, next) => {
    try {
        const { id, oldPassword, newPassword } = req.body;

        const user = await User.findById(id).select('_id password').where('status').equals(status_active);
        if (!user) return res.status(404).json({ message: 'User not found' });

        const isMatch = await AuthServices.comparePasswords(oldPassword, user.password);
        if (!isMatch) return res.status(404).json({ message: 'Incorrect current password' });

        const hashPassword = await AuthServices.hashPassword(newPassword);

        user.password = hashPassword;
        user.password_text = newPassword,
            await user.save();

        return res.status(200).json({ message: 'Password updated successfully', data: true });
    } catch (err) {
        next(err);
    }
}

exports.permssion = async (req, res, next) => {
    const { id } = req.params;
    try {
        const userData = await User.findById(id).select('_id name');
        if (!userData) return res.status(404).json({ message: `User not found!`, data: [] });

        const UserPermissions = await UserPermission.find({ user: userData?._id })
            .select('_id user menu add view edit delete');

        if (!userData) return res.status(404).json({ message: `User has no permissions!`, data: [] });

        res.status(200).json({ message: `User permissions founded`, data: { ...UserPermissions }, title: `${userData?.name} user permissions` });
    } catch (err) { next(err) }
}

exports.update_permssion = async (req, res, next) => {
    const { id } = req.params;
    try {
        const { menu, add, edit, view, delete: del } = req.body;

        const userData = await User.findById(id).select('_id name');
        if (!userData) return res.status(404).json({ message: `User not found!`, data: [] });

        await UserPermission.deleteMany({ user: userData?._id });

        const insertData = menu?.map((m, index) => ({
            user: userData?._id,
            menu: m,
            view: view?.[index] || false,
            add: add?.[index] || false,
            edit: edit?.[index] || false,
            delete: del?.[index] || false,
        }));

        await UserPermission.insertMany(insertData);
        res.status(200).json({ message: `User permissions updated`, data: {}, title: `${userData?.name} user permissions` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? User.findById(identifier): User.findOne({ slug: identifier });
    
    const unitData = await query        
            .select('_id name email dial_code phone gender image status role about updated_by slug')
            .populate('role', '_id name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name')
            .populate('image', '_id name path');

    if (!unitData) return res.status(404).json({ message: `Unit not found` });
    return unitData;
}