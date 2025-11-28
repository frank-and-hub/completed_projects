const mongoose = require('mongoose');

const Color = require('../models/color');
const User = require('../models/user');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'colors/';

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
                { hex_code: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Color.countDocuments({
            ...filter
        });

        const query = Color.find(filter)
            .select('_id name hex_code user status updated_by')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const colors = await query;

        if (colors.length === 0) return res.status(200).json({ message: `No colors found`, data: [] });

        const colorPromises = colors.map(async (color) => {
            const { _id, name, hex_code, status, user } = color;
            return {
                'id': _id,
                'name': name,
                'hex_code': hex_code,
                'status': status
            }
        });
        const colorResponses = await Promise.all(colorPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: colorResponses
            }, title: 'Color'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create color form`,
            body: {
                'name': 'String',
                'hex_code': 'String',
                'userId': 'SchemaId'
            },
            title: 'Add color'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, hex_code } = req.body;
    try {
        let userId = req?.userData?.id;
        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsColor = await Color.findOne({ name: name, hex_code: hex_code, status: status_active });
        if (existsColor) return res.status(200).json({ message: 'Color already exists' });

        const color = new Color({
            _id: new mongoose.Types.ObjectId(),
            user: userData._id,
            name,
            hex_code
        });
        const newData = await color.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'hex_code': newData?.hex_code,
            'user': userData?.name
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const colorData = await this.findData(id, res);
        const { _id, name, hex_code, user, updated_by, status } = colorData;
        const result = {
            'id': _id,
            'name': name,
            'hex_code': hex_code,
            'user': user,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Color data found`, data: result, title: `View ${name} color detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const colorData = await this.findData(id, res);
        const { _id, name, hex_code, user, updated_by, status } = colorData;
        const result = {
            'id': _id,
            'name': name,
            'hex_code': hex_code,
            'user': user,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Color data found`, data: result, title: `Edit ${name} color detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const color = await this.findData(id, res);
        if (!color) return res.status(404).json({ message: `Color not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (color may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        if (updateOps['user']) {
            const userData = await User.findById(updateOps['user']).select('_id').where('status').equals(status_active);
            if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });
        }

        const result = await Color.updateOne({ _id: color._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedColor = await this.findData(id, res);
            const { _id, name, hex_code, user, updated_by, status } = updatedColor;
            const response = {
                'id': _id,
                'name': name,
                'hex_code': hex_code,
                'user': user,
                'status': status,
                'updated_by': updated_by
            }
            return res.status(200).json({ message: `Color details updated successfully`, data: response });
        }
        res.status(404).json({ message: `Color not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getColor = await Color.findById(id).select('_id').where('status').equals(!status_active);
        if (!getColor) return res.status(404).json({ message: 'Color not found' });

        // const colorData = await Color.deleteOne({ _id: id });
        // if (colorData.deletedCount === 1) {

        const colorData = await Color.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (colorData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'hex_code': 'String',
                    'user': 'ID',
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Color not found` });
    } catch (err) { next(err) }
}

exports.findData = async (id, res) => {
    
    const colorData = await Color.findById(id)
        .select('_id name hex_code user updated_by status')
        .populate('user', '_id name.first_name name.middle_name name.last_name')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!colorData) return res.status(404).json({ message: `Color not found` });
    return colorData;
}