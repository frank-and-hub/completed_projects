const mongoose = require('mongoose');

const Warranty = require('../models/warranty');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

const constName = 'warranty/';

exports.index = async (req, res, next) => {
    try {

        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
        const { status, search, period } = req.query;

        if (status) filter.status = status;
        if (period) filter.period = period;

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                { name: { $regex: trimmedSearch, $options: "i" } },
                { slug: { $regex: trimmedSearch, $options: "i" } },
                { description: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Warranty.countDocuments({
            ...filter
        });

        const query = Warranty.find(filter)
            .select('_id name description duration period updated_by status slug')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }
        const warranties = await query;

        if (warranties.length === 0) return res.status(200).json({ message: `No warranties found`, data: [] });

        const warrantyPromises = warranties.map(async (warranty) => {
            const { _id, name, description, duration, period, updated_by, status, slug} = warranty
            return {
                'id': _id,
                'name': name,
                'description': description,
                'duration': duration,
                'period': period,
                'status': status,
                'slug': slug
            }
        });
        const warrantyResponses = await Promise.all(warrantyPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: warrantyResponses
            }, title: 'Warranty'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create warranty form`,
            body: {
                'name': 'String',
                'description': 'String',
                'duration': 'String',
                'period': 'String',
            },
            title: 'Add warranty'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, description, duration, period } = req.body;
    try {

        const existsWarranty = await Warranty.findOne({ name: name, status: status_active });
        if (existsWarranty) return res.status(200).json({ message: 'Warranty already exists' });

        const warranty = new Warranty({
            _id: new mongoose.Types.ObjectId(),
            name, description, duration, period,
        });
        const newData = await warranty.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'description': newData?.description,
            'duration': newData?.duration,
            'period': newData?.period,
        }
        res.status(201).json({ message: `Your name is received Successfully`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const warrantyData = await this.findData(id, res);
        const { _id, name, description, duration, period, updated_by, status } = warrantyData;
        const result = {
            'id': _id,
            'name': name,
            'description': description,
            'duration': duration,
            'period': period,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Warranty data found`, data: result, title: `View ${name} warranty detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const warrantyData = await this.findData(id, res);
        const { _id, name, description, duration, period, updated_by, status } = warrantyData;
        const result = {
            'id': _id,
            'name': name,
            'description': description,
            'duration': duration,
            'period': period,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Warranty data found`, data: result, title: `Edit ${name} warranty detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const warranty = await this.findData(id, res);
        if (!warranty) return res.status(404).json({ message: `Warranty not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (warranty may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const result = await Warranty.updateOne({ _id: warranty._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedWarranty = await this.findData(id, res);
            const { _id, name, description, duration, period } = updatedWarranty;
            const warrantyData = {
                'id': _id,
                'name': name,
                'description': description,
                'duration': duration,
                'period': period,
            }
            return res.status(200).json({ message: `Warranty details updated successfully`, data: warrantyData });
        }
        res.status(404).json({ message: `Warranty not found or no details to update`, data: [] });
    } catch (err) {
        ``
        next(err)
    }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getWarranty = await Warranty.findById(id).select('_id').where('status').equals(!status_active);
        if (!getWarranty) return res.status(404).json({ message: 'Warranty not found' });

        // const warrantyData = await Warranty.deleteOne({ _id: id });
        // if (warrantyData.deletedCount === 1) {

        const warrantyData = await Warranty.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (warrantyData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'description': 'String',
                    'duration': 'String',
                    'period': 'String',
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Warranty not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Warranty.findById(identifier): Warranty.findOne({ slug: identifier });
    
    const warrantyData = await query
        .select('_id name description duration period status updated_by slug')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!warrantyData) return res.status(404).json({ message: `Warranty not found` });
    return warrantyData;
}