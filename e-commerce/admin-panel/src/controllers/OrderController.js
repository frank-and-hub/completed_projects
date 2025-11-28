const mongoose = require('mongoose');

const User = require('../models/user');
const Order = require('../models/order');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'orders/';

exports.index = async (req, res, next) => {
    try {

        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
        const role = req?.userData?.role;
        const { status, search, user_id } = req.query;

        if (status) filter.status = status;
        if (role) {
            if (user_id) filter.user = user_id;
        }

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                { name: { $regex: trimmedSearch, $options: "i" } },
                { price: { $regex: trimmedSearch, $options: "i" } },
                { address: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Order.countDocuments({
            ...filter
        });

        const query = Order.find(filter)
            .select('_id cart user address price time status updated_by')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('cart', '_id products')
            .populate({
                path: 'cart',
                select: '_id products',
                populate: {
                    path: 'products.product',
                    select: '_id name price',
                }
            })
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const orders = await query;

        if (orders.length === 0) return res.status(200).json({ message: `No orders found`, data: [] });

        const orderPromises = orders.map(async (order) => {
            const { _id, cart, user, address, price, status, time } = order;
            return {
                'id': _id,
                'time': time,
                'cart': cart,
                'user': user,
                'price': price,
                'status': status,
                'address': address,
            }
        });
        const orderResponses = await Promise.all(orderPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: orderResponses
            }, title: 'Order'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create order form`,
            body: {
                'cartId': 'SchemaId',
                'userId': 'SchemaId',
                'price': 'Number',
                'address': 'Objects',
                'status': 'String'
            },
            title: 'Add order'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { cart_id, address, price, status } = req.body;
    try {
        let userId = req?.userData?.id;
        const userData = await User.findById(userId).select('_id name').where('status').equals(status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const cartData = await Cart.findById(cart_id).select('_id');
        if (!cartData) return res.status(401).json({ message: `Cart not found!`, data: [] });

        const existsOrder = await Order.findOne({
            user: userData._id,
            cart: cartData._id,
            price: price,
            status: status
        });
        if (existsOrder) return res.status(200).json({ message: 'Order already exists' });

        const order = new Order({
            _id: new mongoose.Types.ObjectId(),
            time: now(),
            price,address,
            user: userData._id,
            cart: cartData._id,
        });
        const newData = await order.save();

        await Cart.updateOne({ _id: cartData._id }, { $set: { status: !status_active } });

        const response = {
            'id': newData?._id,
            'time': newData?.time,
            'price': newData?.price,
            'address': newData?.address,
            'user': userData?.name,
            'cart': cartData?.products,
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const orderData = await this.findData(id, res);
        const { _id, cart, user, address, price, status, time } = orderData;
        const result = {
            'id': _id,
            'cart': cart,
            'user': user,
            'price': price,
            'address': address,
            'time': time,
            'status': status
        }
        res.status(200).json({ message: `Order data found`, data: result, title: `View ${user.name} order detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const orderData = await this.findData(id, res);
        const { _id, cart, user, address, price, status, time } = orderData;
        const result = {
            'id': _id,
            'cart': cart,
            'user': user,
            'price': price,
            'address': address,
            'time': time,
            'status': status
        }
        res.status(200).json({ message: `Order data found`, data: result, title: `Edit ${user.name} order detail` });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getOrder = await Order.findById(id).select('_id').where('status').equals(!status_active);
        if (!getOrder) return res.status(404).json({ message: 'Order not found' });

        // const orderData = await Order.deleteOne({ _id: id });
        // if (orderData.deletedCount === 1) {

        const orderData = await Order.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (orderData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'cartId': 'SchemaId',
                    'userId': 'SchemaId',
                    'price': 'Number',
                    'address': 'Objects',
                    'status': 'String'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Order not found` });
    } catch (err) { next(err) }
}

exports.findData = async (id, res) => {
   
    const orderData = await Order.findById(id)
        .select('_id cart user address price time status updated_by')
        .populate('user', '_id name.first_name name.middle_name name.last_name')
        .populate('cart', '_id products')
        .populate({
            path: 'cart',
            select: '_id products',
            populate: {
                path: 'products.product',
                select: '_id name price',
            }
        })
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!orderData) return res.status(404).json({ message: `Order not found` });
    return orderData;
}