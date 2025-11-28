const mongoose = require('mongoose');

const Category = require('../models/category');
const User = require('../models/user');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'categories/';

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
                { description: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Category.countDocuments({
            ...filter
        });

        const query = Category.find(filter)
            .select('_id name icon description user status updated_by slug')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const categories = await query;

        if (categories.length === 0) return res.status(200).json({ message: `No categories found`, data: [] });

        const categoryPromises = categories.map(async (category) => {
            const { _id, name, icon, description, status, user, slug} = category;
            return {
                'id': _id,
                'name': name,
                'icon': icon,
                'description': description,
                'status': status,
                'slug': slug
            }
        });
        const categoryResponses = await Promise.all(categoryPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: categoryResponses
            }, title: 'Category'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create category form`,
            body: {
                'name': 'String',
                'description': 'String',
                'icon': 'String',
                'userId': 'SchemaId'
            },
            title: 'Add category'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, description, icon } = req.body;
    try {
        let userId = req?.userData?.id;

        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsCategory = await Category.findOne({ name: name, status: status_active, user: userData._id });
        if (existsCategory) return res.status(200).json({ message: 'Category already exists' });

        const category = new Category({
            _id: new mongoose.Types.ObjectId(),
            user: userData._id,
            name,
            description,
            icon
        });
        const newData = await category.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'icon': newData?.icon,
            'description': newData?.description,
            'user': userData?.name
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const categoryData = await this.findData(id, res);
        const { _id, name, icon, description, user, updated_by, status } = categoryData;
        const result = {
            'id': _id,
            'name': name,
            'icon': icon,
            'description': description,
            'user': user,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Category data found`, data: result, title: `View ${name} category detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const categoryData = await this.findData(id, res);
        const { _id, name, icon, description, user, updated_by, status } = categoryData;
        const result = {
            'id': _id,
            'name': name,
            'icon': icon,
            'description': description,
            'user': user,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Category data found`, data: result, title: `Edit ${name} category detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const category = await Category.findById(id).select('_id');
        if (!category) return res.status(404).json({ message: `Category not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (category may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        if (updateOps['user']) {
            const userData = await User.findById(updateOps['user']).select('_id').where('status').equals(status_active);
            if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });
        }

        const result = await Category.updateOne({ _id: id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedCategory = await this.findData(id, res);
            const { _id, name, description, icon, user } = updatedCategory;
            const response = {
                'id': _id,
                'name': name,
                'description': description,
                'icon': icon,
                'user': user
            }
            return res.status(200).json({ message: `Category details updated successfully`, data: response });
        }
        res.status(404).json({ message: `Category not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getCategory = await Category.findById(id).select('_id').where('status').equals(!status_active);
        if (!getCategory) return res.status(404).json({ message: 'Category not found' });

        // const categoryData = await Category.deleteOne({ _id: id });
        // if (categoryData.deletedCount === 1) {

        const categoryData = await Category.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (categoryData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'description': 'String',
                    'icon': 'String',
                    'user': 'ID',
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Category not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Category.findById(identifier): Category.findOne({ slug: identifier });
    const categoryData = await query.select('_id name icon description user updated_by status slug')
        .populate('user', '_id name.first_name name.middle_name name.last_name')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!categoryData) return res.status(404).json({ message: `Category not found` });
    return categoryData;
}