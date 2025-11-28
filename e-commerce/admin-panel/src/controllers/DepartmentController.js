const mongoose = require('mongoose');

const Department = require('../models/department');
const User = require('../models/user');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

const constName = 'department/';

exports.index = async (req, res, next) => {
    try {

        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
        const { status, search } = req.query;

        if (status) filter.status = status;

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                { name: { $regex: trimmedSearch, $options: "i" } },
                { slug: { $regex: trimmedSearch, $options: "i" } },
                { short_name: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Department.countDocuments({
            ...filter
        });

        const query = Department.find(filter)
            .select('_id name icon hod updated_by status slug')
            .populate('hod', '_id name.first_name name.middle_name name.last_name')
            .populate({
                path: 'members',
                select: '_id name.first_name name.middle_name name.last_name'
            })
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }
        const departments = await query;

        if (departments.length === 0) return res.status(200).json({ message: `No departments found`, data: [] });

        const departmentPromises = departments.map(async (department) => {
            const { _id, name, icon, hod, members, updated_by, status, slug} = department
            return {
                'id': _id,
                'name': name,
                'icon': icon,
                'hod': `${hod.name?.first_name} ${hod?.name?.middle_name} ${hod?.name?.last_name}`,
                'team_members': members.length,
                'status': status,
                'slug':slug
            }
        });
        const departmentResponses = await Promise.all(departmentPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: departmentResponses
            }, title: 'Department'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create department form`,
            body: {
                'name': 'String',
                'icon': 'String',
                'hod': 'SchemaId',
                'members': 'Array of SchemaId'
            },
            title: 'Add department'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, icon, hod_id, members } = req.body;
    try {
        const existsDepartment = await Department.findOne({ name: name, status: status_active });
        if (existsDepartment) return res.status(200).json({ message: 'Department already exists' });

        const hod = await User.findById(hod_id).select('_id name.first_name name.middle_name name.last_name').where('status').equals(status_active);
        if (!hod) return res.status(404).json({ message: `User not found` });

        const foundMembers = await User.find({ _id: { $in: members }, status: status_active }).select('_id name.first_name name.middle_name name.last_name');
        if (foundMembers.length !== members.length) return res.status(404).json({ message: 'One or more active users not found' });

        const department = new Department({
            _id: new mongoose.Types.ObjectId(),
            name, icon,
            hod: hod_id,
            members: foundMembers.map(p => p._id)
        });

        const newData = await department.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'icon': newData.icon,
            'hod': hod?.name,
            'members': foundMembers.map(perm => ({
                'id': perm._id,
                'name': `${perm.name?.first_name} ${perm?.name?.middle_name} ${perm?.name?.last_name}`,
            }))
        }
        res.status(201).json({ message: `Your name is received Successfully`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const departmentData = await this.findData(id, res);
        const { _id, name, icon, hod, members, updated_by, status } = departmentData;
        const result = {
            'id': _id,
            'name': name,
            'icon': icon,
            'hod_id': hod?._id,
            'members': await helper.filterData(members),
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Department data found`, data: result, title: `View ${name} department detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const departmentData = await this.findData(id, res);
        const { _id, name, icon, hod, members, updated_by, status } = departmentData;
        const result = {
            'id': _id,
            'name': name,
            'icon': icon,
            'hod_id': hod?._id,
            'members': await helper.filterData(members),
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Department data found`, data: result, title: `Edit ${name} department detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const department = await this.findData(id, res);
        if (!department) return res.status(404).json({ message: `Department not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (department may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const membersId = updateOps['members'];
        const hodId = updateOps['hod_id'];

        if (hodId) {
            updateOps['hod'] = hodId;
        }

        const members = await User.find({ _id: { $in: membersId } }).select('_id name.first_name name.middle_name name.last_name');
        if (membersId && members?.length !== membersId?.length) return res.status(404).json({ message: 'One or more members not found' });

        const result = await Department.updateOne({ _id: department._id }, { $set: updateOps });

        if (result.modifiedCount > 0) {
            const updatedDepartment = await this.findData(id, res);
            const result = {
                'id': updatedDepartment._id,
                'name': updatedDepartment?.name,
                'members': updatedDepartment?.members.map(per => ({
                    'id': per._id,
                    'name': `${per.name?.first_name} ${per?.name?.middle_name} ${per?.name?.last_name}`,
                }))
            }
            return res.status(200).json({ message: `Department details updated successfully`, data: result });
        }
        res.status(404).json({ message: `Department not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getDepartment = await Department.findById(id).select('_id').where('status').equals(!status_active);
        if (!getDepartment) return res.status(404).json({ message: 'Department not found' });

        // const departmentData = await Department.deleteOne({ _id: id });
        // if (departmentData.deletedCount === 1) {

        const departmentData = await Department.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (departmentData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'icon': 'String',
                    'hod': 'SchemaId',
                    'members': 'Array of SchemaId'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Department not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Department.findById(identifier): Department.findOne({ slug: identifier });
    
    const departmentData = await query.select('_id name icon hod members updated_by status slug')
        .populate('hod', '_id name.first_name name.middle_name name.last_name')
        .populate({
            path: 'members',
            select: '_id name.first_name name.middle_name name.last_name'
        })
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!departmentData) return res.status(404).json({ message: `Department not found` });
    return departmentData;
}