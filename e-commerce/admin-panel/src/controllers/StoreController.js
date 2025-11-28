const mongoose = require('mongoose');

const Store = require('../models/store');
const User = require('../models/user');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

const constName = 'store/';

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
                // { phone: { $regex: trimmedSearch, $options: "i" } },
                { slug: { $regex: trimmedSearch, $options: "i" } },
                { email: { $regex: trimmedSearch, $options: "i" } },
                { address: { $regex: trimmedSearch, $options: "i" } },
                { city: { $regex: trimmedSearch, $options: "i" } },
                // { zipcode: { $regex: trimmedSearch, $options: "i" } },
                { state: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Store.countDocuments({
            ...filter
        });

        const query = Store.find(filter)
            .select('_id name phone email address city state zipcode country supplier updated_by status slug')
            .populate('supplier', '_id name.first_name name.middle_name name.last_name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }
        const stores = await query;

        if (stores.length === 0) return res.status(200).json({ message: `No stores found`, data: [] });

        const storePromises = stores.map(async (store) => {
            const { _id, name, phone, email, address, city, state, zipcode, country, supplier, status, slug} = store
            return {
                'id': _id,
                'name': name,
                'phone': phone,
                'email': email,
                // 'address': address,
                'city': city,
                // 'state': state,
                // 'country': country,
                // 'zipcode': zipcode,
                'status': status,
                'slug': slug
            }
        });
        const storeResponses = await Promise.all(storePromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: storeResponses
            }, title: 'Store'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create store form`,
            body: {
                'name': 'String',
                'phone': 'Number',
                'email': 'String',
                'address': 'String',
                'city': 'String',
                'country': 'String',
                'state': 'String',
                'zipcode': 'Number',
                'supplier': 'SchemaID'
            },
            title: 'Add store'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, phone, email, address, city, state, zipcode, supplier_id, country } = req.body;
    try {

        const existsStore = await Store.findOne({ name: name, status: status_active, address: address });
        if (existsStore) return res.status(200).json({ message: 'Store already exists' });

        const supplier = await User.findById(supplier_id).select('_id name').where('status').equals(status_active);
        if (!supplier) return res.status(404).json({ message: `Supplier not found` });

        const store = new Store({
            _id: new mongoose.Types.ObjectId(),
            name, phone, email, address, city, state, zipcode, country,
            supplier: supplier._id,
        });
        const newData = await store.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'phone': newData?.phone,
            'email': newData?.email,
            'address': newData?.address,
            'country': newData?.country,
            'city': newData?.city,
            'state': newData?.state,
            'zipcode': newData?.zipcode,
            'supplier': supplier.name
        }
        res.status(201).json({ message: `Your name is received Successfully`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const storeData = await this.findData(id, res);
        const { _id, name, phone, email, address, city, state, zipcode, country, supplier, updated_by, status } = storeData;
        const result = {
            'id': _id,
            'name': name,
            'phone': phone,
            'email': email,
            'address': address,
            'country': country,
            'city': city,
            'state': state,
            'zipcode': zipcode,
            'supplier_id': supplier._id,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Store data found`, data: result, title: `View ${name} store detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const storeData = await this.findData(id, res);
        const { _id, name, phone, email, address, city, state, zipcode, country, supplier, updated_by, status } = storeData;
        const result = {
            'id': _id,
            'name': name,
            'phone': phone,
            'email': email,
            'address': address,
            'country': country,
            'city': city,
            'state': state,
            'zipcode': zipcode,
            'supplier_id': supplier._id,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Store data found`, data: result, title: `Edit ${name} store detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const store = await this.findData(id, res);
        if (!store) return res.status(404).json({ message: `Store not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (store may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const result = await Store.updateOne({ _id: store._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedStore = await this.findData(id, res);
            const { _id, name, phone, email, address, city, state, zipcode, country, supplier } = updatedStore;
            const storeData = {
                'id': _id,
                'name': name,
                'phone': phone,
                'email': email,
                'address': address,
                'country': country,
                'city': city,
                'state': state,
                'zipcode': zipcode,
                'supplier': supplier,
            }
            return res.status(200).json({ message: `Store details updated successfully`, data: storeData });
        }
        res.status(404).json({ message: `Store not found or no details to update`, data: [] });
    } catch (err) {
        ``
        next(err)
    }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getStore = await Store.findById(id).select('_id').where('status').equals(!status_active);
        if (!getStore) return res.status(404).json({ message: 'Store not found' });

        // const storeData = await Store.deleteOne({ _id: id });
        // if (storeData.deletedCount === 1) {

        const storeData = await Store.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (storeData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'phone': 'Number',
                    'email': 'String',
                    'address': 'String',
                    'city': 'String',
                    'country': 'String',
                    'state': 'String',
                    'zipcode': 'Number',
                    'supplier': 'SchemaID'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Store not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Store.findById(identifier): Store.findOne({ slug: identifier });
    
    const storeData = await query
        .select('_id name phone email address country city state zipcode status supplier updated_by slug')
        .populate('supplier', '_id')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!storeData) return res.status(404).json({ message: `Store not found` });
    return storeData;
}