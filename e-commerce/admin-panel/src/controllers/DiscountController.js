const mongoose = require('mongoose');

const Discount = require('../models/discount');
const User = require('../models/user');


// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'discounts/';

exports.index = async (req, res, next) => {
    const { search, status } = req.query;
    try {
        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
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
        const totalCount = await Discount.countDocuments({
            ...filter
        });

        const query = Discount.find(filter)
            .select('_id name description percentage user status updated_by slug')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const discounts = await query;
        if (discounts.length === 0) return res.status(200).json({ message: `No discounts found`, data: [] });

        const discountPromises = discounts.map(async (discount) => {
            const { _id, name, description, percentage, status, slug} = discount;

            return {
                'id': _id,
                'name': name,
                'percentage': percentage,
                'description': description,
                'status': status,
                'slug': slug
            }
        });
        const discountResponses = await Promise.all(discountPromises);
        res.status(200).json({
            message: `Discounts list retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: discountResponses
            }, title: 'Discount'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create discount form`,
            body: {
                'name': 'String',
                'description': 'String',
                'percentage': 'Number',
                'user': 'SchemaId'
            },
            title: 'Add discount'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, description, percentage } = req.body;
    try {
        let userId = req?.userData?.id;

        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsDiscount = await Discount.findOne({ name: name, status: status_active, user: userData._id });
        if (existsDiscount) return res.status(200).json({ message: 'Discount already exists' });

        const discount = new Discount({
            _id: new mongoose.Types.ObjectId(),
            name, description, percentage,
            user: userData._id,
        });
        const newDiscount = await discount.save();
        const response = {
            'id': newDiscount?._id,
            'name': newDiscount?.name,
            'percentage': newDiscount?.percentage,
            'description': newDiscount?.description,
            'user': userData?.name
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const discountData = await this.findData(id, res);
        const { _id, name, description, percentage, updated_by, status, user } = discountData;
        const result = {
            'id': _id,
            'name': name,
            'percentage': percentage,
            'description': description,
            'user': user,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Discount data found`, data: result, title: `View ${name} discount detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const discountData = await this.findData(id, res);
        const { _id, name, description, percentage, updated_by, status, user } = discountData;
        const result = {
            'id': _id,
            'name': name,
            'percentage': percentage,
            'description': description,
            'user': user,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Discount data found`, data: result, title: `Edit ${name} discount detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {

        const discount = await this.findData(id, res);
        if (!discount) return res.status(404).json({ message: `Discount not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (discount may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        if (updateOps['user']) {
            const userData = await User.findById(updateOps['user']).select('_id').where('status').equals(status_active);
            if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });
        }

        const result = await Discount.updateOne({ _id: discount._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedDiscount = await this.findData(id, res);
            const { _id, name, description, percentage, updated_by, status, user } = updatedDiscount;
            const response = {
                'id': _id,
                'name': name,
                'percentage': percentage,
                'description': description,
                'user': user,
                'status': status,
                'updated_by': updated_by
            }
            return res.status(200).json({ message: `Discount details updated successfully`, data: response });
        }
        res.status(404).json({ message: `Discount not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getDiscount = await Discount.findById(id).select('_id').where('status').equals(!status_active);
        if (!getDiscount) return res.status(404).json({ message: 'Discount not found' });

        // const discountData = await Discount.deleteOne({ _id: id });
        // if (discountData.deletedCount === 1) {

        const discountData = await Discount.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (discountData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'description': 'String',
                    'percentage': 'Number',
                    'user': 'SchemaId'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Discount not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Discount.findById(identifier): Discount.findOne({ slug: identifier });
    
    const discountData = await query
        .select('_id name description percentage updated_by status user slug')
        .populate('user', '_id name.first_name name.middle_name name.last_name')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!discountData) return res.status(404).json({ message: `Discount not found` });
    return discountData;
}