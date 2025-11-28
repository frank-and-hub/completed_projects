const mongoose = require('mongoose');

const User = require('../models/user');
const Product = require('../models/product');
const WishList = require('../models/wish_list');

// base url
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

exports.index = async (req, res, next) => {
    try {
        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        let user_id = req?.userData?.id;
        const filter = { deleted_at: null };
        const { status, search } = req.query;

        if (status) filter.status = status;
        if (user_id) filter.user = user_id;

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                { products: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await WishList.countDocuments({
            ...filter
        });

        const query = WishList.find(filter)
            .select('_id products user status updated_by')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name')
            .populate('products', '_id name price');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const wishLists = await query;

        if (wishLists.length === 0) return res.status(200).json({ message: `wish_lists is empty!!`, data: [] });

        const wishListPromises = wishLists.map(async (wishList) => {
            const { _id, products, status } = wishList;
            return {
                'id': _id,
                'products': products.map(p => ({
                    'id': p._id,
                    'name': p.name,
                    'price': p.price
                })),
                'status': status
            }
        });
        const wishListResponses = await Promise.all(wishListPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: wishListResponses
            }, title: 'Wish List'
        });
    } catch (err) { next(err)  }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create wishList form`,
            body: {
                'products': 'Array of SchemaId',
                'userId': 'SchemaId'
            },
            title: 'Add wishList'
        });
    } catch (err) { next(err)  }
}

exports.store = async (req, res, next) => {
    const { product_id } = req.body;
    try {
        let msg = ``;
        const response = [];
        const user_id = req?.userData?.id;

        const userData = await User.findById(user_id).select('_id name').where('status').equals(status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const productData = await Product.findById(product_id).select('_id name').where('status').equals(status_active);
        if (!productData) return res.status(401).json({ message: `Product not found!`, data: [] });

        const existsWishList = await WishList.findOne({ products: products, user: userData._id, status: status_active });

        if (existsWishList) {
            const wishListData = await WishList.deleteOne({ _id: existsWishList._id });
            if (wishListData.deletedCount === 1) res.status(201).json({ message: `Deleted successfully`, data: [] });
        };

        let wishList = await Cart.findOne({
            user: userData._id
        });

        if (!wishList) {
            wishList = new WishList({
                _id: new mongoose.Types.ObjectId(),
                user: userData._id,
                products: [productData._id]
            });
            const newData = await wishList.save();
            response = {
                'id': newData?._id,
                'products': productData.map(perm => ({
                    'id': perm._id,
                    'name': perm.name
                })),
                'user': userData?.name
            }
            msg = `Successfully created new wish list`;
        } else {

            wishList.products.push(productData._id);
            await wishList.save();
            const productIds = wishList.products.map(p => p.product); // Extract the product IDs from the cart
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
                })),
                'user': userData?.name
            };
            msg = `Product added to wish list.`;
        }
        res.status(201).json({ message: msg, data: response });
    } catch (err) { next(err)  }
}
