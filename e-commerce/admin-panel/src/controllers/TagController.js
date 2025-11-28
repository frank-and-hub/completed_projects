const mongoose = require('mongoose');

const Tag = require('../models/tag');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

const constName = 'tag/';

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
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Tag.countDocuments({
            ...filter
        });

        const query = Tag.find(filter)
            .select('_id name updated_by status slug')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }
        const tags = await query;

        if (tags.length === 0) return res.status(200).json({ message: `No tags found`, data: [] });

        const tagPromises = tags.map(async (tag) => {
            const { _id, name, updated_by, status, slug} = tag
            return {
                'id': _id,
                'name': name,
                'updated_by': updated_by,
                'status': status,
                'slug': slug
            }
        });
        const tagResponses = await Promise.all(tagPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: tagResponses
            }, title: 'Tag'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create tag form`,
            body: {
                'name': 'String',
            },
            title: 'Add tag'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name } = req.body;
    try {

        const existsTag = await Tag.findOne({ name: name, status: status_active });
        if (existsTag) return res.status(200).json({ message: 'Tag already exists' });

        const tag = new Tag({
            _id: new mongoose.Types.ObjectId(),
            name,
        });
        const newData = await tag.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
        }
        res.status(201).json({ message: `Your name is received Successfully`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const tagData = await this.findData(id, res);
        const { _id, name, updated_by, status } = tagData;
        const result = {
            'id': _id,
            'name': name,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Tag data found`, data: result, title: `View ${name} tag detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const tagData = await this.findData(id, res);
        const { _id, name, updated_by, status } = tagData;
        const result = {
            'id': _id,
            'name': name,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Tag data found`, data: result, title: `Edit ${name} tag detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const tag = await this.findData(id, res);
        if (!tag) return res.status(404).json({ message: `Tag not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (tag may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const result = await Tag.updateOne({ _id: tag._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedTag = await this.findData(id, res);
            const { _id, name } = updatedTag;
            const tagData = {
                'id': _id,
                'name': name,
            }
            return res.status(200).json({ message: `Tag details updated successfully`, data: tagData });
        }
        res.status(404).json({ message: `Tag not found or no details to update`, data: [] });
    } catch (err) {
        ``
        next(err)
    }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getTag = await Tag.findById(id).select('_id').where('status').equals(!status_active);
        if (!getTag) return res.status(404).json({ message: 'Tag not found' });

        // const tagData = await Tag.deleteOne({ _id: id });
        // if (tagData.deletedCount === 1) {

        const tagData = await Tag.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (tagData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Tag not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Tag.findById(identifier): Tag.findOne({ slug: identifier });
    
    const tagData = await query
        .select('_id name status updated_by slug')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!tagData) return res.status(404).json({ message: `Tag not found` });
    return tagData;
}