const mongoose = require('mongoose');

const Unit = require('../models/unit');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

const constName = 'unit/';

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
        const totalCount = await Unit.countDocuments({
            ...filter
        });

        const query = Unit.find(filter)
            .select('_id name short_name updated_by status slug')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }
        const units = await query;

        if (units.length === 0) return res.status(200).json({ message: `No units found`, data: [] });

        const unitPromises = units.map(async (unit) => {
            const { _id, name, short_name, status, slug} = unit
            return {
                'id': _id,
                'name': name,
                'short_name': short_name,
                'status': status,
                'slug': slug
            }
        });
        const unitResponses = await Promise.all(unitPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: unitResponses
            }, title: 'Unit'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create unit form`,
            body: {
                'name': 'String',
                'short_name': 'String',
            },
            title: 'Add unit'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, short_name } = req.body;
    try {

        const existsUnit = await Unit.findOne({ name: name, status: status_active });
        if (existsUnit) return res.status(200).json({ message: 'Unit already exists' });

        const unit = new Unit({
            _id: new mongoose.Types.ObjectId(),
            name, short_name,
        });
        const newData = await unit.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'short_name': newData?.short_name
        }
        res.status(201).json({ message: `Your name is received Successfully`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const unitData = await this.findData(id, res);
        const { _id, name, short_name, updated_by, status } = unitData;
        const result = {
            'id': _id,
            'name': name,
            'short_name': short_name,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Unit data found`, data: result, title: `View ${name} unit detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const unitData = await this.findData(id, res);
        const { _id, name, short_name, updated_by, status } = unitData;
        const result = {
            'id': _id,
            'name': name,
            'short_name': short_name,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Unit data found`, data: result, title: `Edit ${name} unit detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const unit = await this.findData(id, res);
        if (!unit) return res.status(404).json({ message: `Unit not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (unit may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const result = await Unit.updateOne({ _id: unit._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedUnit = await this.findData(id, res);
            const { _id, name, short_name } = updatedUnit;
            const unitData = {
                'id': _id,
                'name': name,
                'short_name': short_name
            }
            return res.status(200).json({ message: `Unit details updated successfully`, data: unitData });
        }
        res.status(404).json({ message: `Unit not found or no details to update`, data: [] });
    } catch (err) {
        ``
        next(err)
    }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getUnit = await Unit.findById(id).select('_id').where('status').equals(!status_active);
        if (!getUnit) return res.status(404).json({ message: 'Unit not found' });

        // const unitData = await Unit.deleteOne({ _id: id });
        // if (unitData.deletedCount === 1) {

        const unitData = await Unit.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (unitData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'short_name': 'String',
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Unit not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Unit.findById(identifier): Unit.findOne({ slug: identifier });
    
    const unitData = await query
        .select('_id name short_name updated_by status slug')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!unitData) return res.status(404).json({ message: `Unit not found` });
    return unitData;
}