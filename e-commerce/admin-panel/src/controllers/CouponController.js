const mongoose = require('mongoose');

const Coupon = require('../models/coupon');
const Discount = require('../models/discount');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

const constName = 'coupon/';

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
        const totalCount = await Coupon.countDocuments({
            ...filter
        });

        const query = Coupon.find(filter)
            .select('_id name discount code start_date end_date limit once_per_customer updated_by status slug')
            .populate('discount', '_id name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }
        const coupons = await query;

        if (coupons.length === 0) return res.status(200).json({ message: `No coupons found`, data: [] });

        const couponPromises = coupons.map(async (coupon) => {
            const { _id, name, discount, code, start_date, end_date, limit, once_per_customer, updated_by, status, slug} = coupon
            return {
                'id': _id,
                'name': name,
                'discount': discount,
                'code': code,
                'start_date': start_date,
                'end_date': end_date,
                'limit': limit,
                'once_per_customer': once_per_customer,
                'updated_by': updated_by,
                'status': status,
                'slug': slug
            }
        });
        const couponResponses = await Promise.all(couponPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: couponResponses
            }, title: 'Coupon'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create coupon form`,
            body: {
                'name': 'String',
                'discount': 'SchemaId',
                'code': 'String',
                'start_date': 'Date',
                'end_date': 'Date',
                'limit': 'Number',
                'once_per_customer': 'Boolean',
            },
            title: 'Add coupon'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, discount_id, code, start_date, end_date, limit, once_per_customer } = req.body;
    try {

        const existsCoupon = await Coupon.findOne({ name: name, status: status_active });
        if (existsCoupon) return res.status(200).json({ message: 'Coupon already exists' });

        const discount = await Discount.findById(discount_id).select('_id name').where('status').equals(status_active);
        if (!user) return res.status(404).json({ message: `Discount not found` });

        const coupon = new Coupon({
            _id: new mongoose.Types.ObjectId(),
            name, code, start_date, end_date, limit, once_per_customer,
            discount: discount?._id,
        });

        const newData = await coupon.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'discount': discount.name,
            'code': newData?.code,
            'start_date': newData?.start_date,
            'end_date': newData?.end_date,
            'limit': newData?.limit,
            'once_per_customer': newData?.once_per_customer,
        }
        res.status(201).json({ message: `Your name is received Successfully`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const couponData = await this.findData(id, res);
        const { _id, name, discount, code, start_date, end_date, limit, once_per_customer, updated_by, status } = couponData;
        const result = {
            'id': _id,
            'name': name,
            'discount_id': discount?._id,
            'code': code,
            'start_date': start_date,
            'end_date': end_date,
            'limit': limit,
            'once_per_customer': once_per_customer,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Coupon data found`, data: result, title: `View ${name} coupon detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const couponData = await this.findData(id, res);
        const { _id, name, discount, code, start_date, end_date, limit, once_per_customer, updated_by, status } = couponData;
        const result = {
            'id': _id,
            'name': name,
            'discount_id': discount?._id,
            'code': code,
            'start_date': start_date,
            'end_date': end_date,
            'limit': limit,
            'once_per_customer': once_per_customer,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Coupon data found`, data: result, title: `Edit ${name} coupon detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const coupon = await this.findData(id, res);
        if (!coupon) return res.status(404).json({ message: `Coupon not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (coupon may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const result = await Coupon.updateOne({ _id: coupon._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedCoupon = await this.findData(id, res);
            const { _id, name, discount, code, start_date, end_date, limit, once_per_customer } = updatedCoupon;
            const couponData = {
                'id': _id,
                'name': name,
                'discount': discount,
                'code': code,
                'start_date': start_date,
                'end_date': end_date,
                'limit': limit,
                'once_per_customer': once_per_customer,
            }
            return res.status(200).json({ message: `Coupon details updated successfully`, data: couponData });
        }
        res.status(404).json({ message: `Coupon not found or no details to update`, data: [] });
    } catch (err) {
        ``
        next(err)
    }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getCoupon = await Coupon.findById(id).select('_id').where('status').equals(!status_active);
        if (!getCoupon) return res.status(404).json({ message: 'Coupon not found' });

        // const couponData = await Coupon.deleteOne({ _id: id });
        // if (couponData.deletedCount === 1) {

        const couponData = await Coupon.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (couponData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'discount': 'SchemaId',
                    'code': 'String',
                    'start_date': 'Date',
                    'end_date': 'Date',
                    'limit': 'Number',
                    'once_per_customer': 'Boolean',
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Coupon not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Coupon.findById(identifier): Coupon.findOne({ slug: identifier });
    
    const couponData = await query
        .select('_id name discount code start_date end_date limit once_per_customer status updated_by slug')
        .populate('discount', '_id name')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!couponData) return res.status(404).json({ message: `Coupon not found` });
    return couponData;
}