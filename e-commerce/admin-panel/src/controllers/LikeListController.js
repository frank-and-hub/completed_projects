const mongoose = require('mongoose');

const Like = require('../models/like_list');
const User = require('../models/user');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'like_lists/';

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
                { hex_code: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Like.countDocuments({
            ...filter
        });

        const query = Like.find(filter)
            .select('_id product user status updated_by')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('product', '_id name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const like_lists = await query;

        if (like_lists.length === 0) return res.status(200).json({ message: `No like_lists found`, data: [] });

        const likeListPromises = like_lists.map(async (like_list) => {
            const { _id, product, status } = like_list;
            return {
                'id': _id,
                'product': product,
                'status': status
            }
        });
        const likeListResponses = await Promise.all(likeListPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: likeListResponses
            }, title: 'Like List'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create like_list form`,
            body: {
                'name': 'String',
                'hex_code': 'String',
                'userId': 'SchemaId'
            },
            title: 'Add like_list'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, hex_code } = req.body;
    try {
        let userId = req?.userData?.id;
        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsLike = await Like.findOne({ name: name, hex_code: hex_code, status: status_active });
        if (existsLike) return res.status(200).json({ message: 'Like already exists' });

        const like_list = new Like({
            _id: new mongoose.Types.ObjectId(),
            user: userData._id,
            name,
            hex_code
        });
        const newData = await like_list.save();
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
        const likeListData = await this.findData(id, res);
        const { _id, name, hex_code, user, updated_by, status } = likeListData;
        const result = {
            'id': _id,
            'name': name,
            'hex_code': hex_code,
            'user': user,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Like data found`, data: result, title: `View ${name} like_list detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const likeListData = await this.findData(id, res);
        const { _id, name, hex_code, user, updated_by, status } = likeListData;
        const result = {
            'id': _id,
            'name': name,
            'hex_code': hex_code,
            'user': user,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Like data found`, data: result, title: `Edit ${name} like_list detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const like_list = await this.findData(id, res);
        if (!like_list) return res.status(404).json({ message: `Like not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (like_list may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        if (updateOps['user']) {
            const userData = await User.findById(updateOps['user']).select('_id').where('status').equals(status_active);
            if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });
        }

        const result = await Like.updateOne({ _id: like_list._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedLike = await this.findData(id, res);
            const { _id, name, hex_code, user, updated_by, status } = updatedLike;
            const response = {
                'id': _id,
                'name': name,
                'hex_code': hex_code,
                'user': user,
                'status': status,
                'updated_by': updated_by
            }
            return res.status(200).json({ message: `Like details updated successfully`, data: response });
        }
        res.status(404).json({ message: `Like not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getLike = await Like.findById(id).select('_id').where('status').equals(!status_active);
        if (!getLike) return res.status(404).json({ message: 'Like not found' });

        // const likeListData = await Like.deleteOne({ _id: id });
        // if (likeListData.deletedCount === 1) {

        const likeListData = await Like.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (likeListData) {
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
        res.status(404).json({ message: `Like not found` });
    } catch (err) { next(err) }
}

exports.findData = async (id, res) => {

    const likeListData = await Like.findById(id)
        .select('_id product user updated_by status')
        .populate('user', '_id name.first_name name.middle_name name.last_name')
        .populate('product', '_id name')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!likeListData) return res.status(404).json({ message: `Like not found` });
    return likeListData;
}