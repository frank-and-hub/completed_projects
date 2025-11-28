const mongoose = require('mongoose');

const Cart = require('../models/cart');
const User = require('../models/user');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'carts/';

exports.index = async (req, res, next) => {
    try {

        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
        const user_id = req?.userData?.id;

        const { status, search } = req.query;

        if (status) filter.status = status;
        if (user_id) filter.user = user_id;

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                { product: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Cart.countDocuments({
            ...filter
        });

        const query = Cart.find(filter)
            .select('_id product user status updated_by')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('product', '_id name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const carts = await query;

        if (carts.length === 0) return res.status(200).json({ message: `No carts found`, data: [] });

        const cartPromises = carts.map(async (cart) => {
            const { _id, product, status, user } = cart;
            return {
                'id': _id,
                'product': product,
                'status': status
            }
        });

        const cartResponses = await Promise.all(cartPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: cartResponses
            }, title: 'Cart'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create cart form`,
            body: {
                'product': 'SchemaId',
                'user': 'SchemaId'
            },
            title: 'Add cart'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { product_id, quantity = true } = req.body;
    try {
        let msg = ``; let response = {};
        let userId = req?.userData?.id;
        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const productData = await Product.findById(product_id).select('_id name price').where('status').equals(status_active);
        if (!productData) return res.status(401).json({ message: `Product not found!`, data: [] });

        let cart = await Cart.findOne({
            user: userData._id,
            status: status_active
        });

        if (!cart) {
            cart = new Cart({
                _id: new mongoose.Types.ObjectId(),
                user: userData._id,
                products: [{
                    product: productData._id,
                    quantity: 1
                }]
            });
            const newData = await cart.save();
            response = {
                'id': newData?._id,
                'products': {
                    'id': productData._id,
                    'name': productData.name,
                    'price': productData.price,
                    'quantity': 1
                },
                'user': userData?.name
            }
            msg = `Successfully created new cart`;
        } else {
            const productInCartIndex = cart.products.findIndex(p => p.product.toString() === productData._id.toString());
            if (productInCartIndex > -1) {

                cart.products[productInCartIndex].quantity += (quantity ? 1 : -1);
                await cart.save();

                const productIds = cart.products.map(p => p.product); // Extract the product IDs from the cart
                const foundProducts = await Product.find({
                    _id: { $in: productIds },
                    status: status_active
                }).select('_id name price');

                response = {
                    'id': cart?._id,
                    'products': foundProducts.map(product => ({
                        'id': product._id,
                        'name': product.name,
                        'price': product.price,
                        'quantity': cart.products.find(p => p.product.toString() === product._id.toString()).quantity // Get the correct quantity for each product
                    })),
                    'user': userData?.name
                };

                msg = `Product quantity updated in cart.`;
            } else {
                cart.products.push({
                    product: productData._id,
                    quantity: quantity || 1
                });
                await cart.save();

                const productIds = cart.products.map(p => p.product); // Extract the product IDs from the cart
                const foundProducts = await Product.find({
                    _id: { $in: productIds },
                    status: status_active
                }).select('_id name price');

                response = {
                    'id': cart?._id,
                    'products': foundProducts.map(product => ({
                        'id': product._id,
                        'name': product.name,
                        'price': product.price,
                        'quantity': cart.products.find(p => p.product.toString() === product._id.toString()).quantity // Get the correct quantity for each product
                    })),
                    'user': userData?.name
                };
                msg = `Product added to cart.`;
            }
        }
        res.status(201).json({ message: msg, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const cartData = await this.findData(id, res);
        const { _id, products, user, updated_by, status } = cartData;
        const result = {
            'id': _id,
            'status': status,
            'user': user,
            'products': products.map(p => ({
                'product': p.product,
                'quantity': p.quantity
            }))
        }
        res.status(200).json({ message: `Cart data found`, data: result, title: `View ${user.name} cart detail` });
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

exports.findData = async (id, res) => {

    const cartData = await Cart.findById(id)
        .select('_id products user updated_by status')
        .populate('user', '_id name.first_name name.middle_name name.last_name')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name')
        .populate('products.product', '_id name price');

    if (!cartData) return res.status(404).json({ message: `Cart not found` });
    return cartData;
}