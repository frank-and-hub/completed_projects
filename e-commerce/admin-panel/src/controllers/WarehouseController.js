const mongoose = require('mongoose');

const Warehouse = require('../models/warehouse');
const User = require('../models/user');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

const constName = 'warehouse/';

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
        const totalCount = await Warehouse.countDocuments({
            ...filter
        });

        const query = Warehouse.find(filter)
            .select('_id name owner_name phone email address city state zipcode country supplier updated_by status slug')
            .populate('supplier', '_id name.first_name name.middle_name name.last_name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }
        const warehouses = await query;

        if (warehouses.length === 0) return res.status(200).json({ message: `No warehouses found`, data: [] });

        const warehousePromises = warehouses.map(async (warehouse) => {
            const { _id, name, owner_name, phone, email, address, city, state, zipcode, slug, updated_by, status } = warehouse
            return {
                'id': _id,
                'name': name,
                'owner_name': owner_name,
                'phone': phone,
                'email': email,
                // 'address': address,
                'city': city,
                // 'state': state,
                // 'zipcode': zipcode,
                'slug': slug,
                // 'updated_by': updated_by,
                'status': status
            }
        });
        const warehouseResponses = await Promise.all(warehousePromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: warehouseResponses
            }, title: 'Warehouse'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create warehouse form`,
            body: {
                'name': 'String',
                'owner_name': 'Strng',
                'phone': 'Number',
                'email': 'String',
                'address': 'String',
                'city': 'String',
                'state': 'String',
                'zipcode': 'Number',
                'supplier': 'SchemaID'
            },
            title: 'Add warehouse'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, owner_name, phone, email, address, city, state, zipcode, supplier_id } = req.body;
    try {

        const existsWarehouse = await Warehouse.findOne({ name: name, status: status_active });
        if (existsWarehouse) return res.status(200).json({ message: 'Warehouse already exists' });

        const supplier = await User.findById(supplier_id).select('_id name').where('status').equals(status_active);
        if (!supplier) return res.status(404).json({ message: `Supplier not found` });

        const warehouse = new Warehouse({
            _id: new mongoose.Types.ObjectId(),
            name, owner_name, phone, email, address, city, state, zipcode,
            supplier: supplier._id,
        });
        const newData = await warehouse.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'owner_name': newData?.owner_name,
            'phone': newData?.phone,
            'email': newData?.email,
            'address': newData?.address,
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
        const warehouseData = await this.findData(id, res);
        const { _id, name, owner_name, phone, email, address, city, state, zipcode, supplier, updated_by, status } = warehouseData;
        const result = {
            'id': _id,
            'name': name,
            'owner_name': owner_name,
            'phone': phone,
            'work_phone': phone,
            'email': email,
            'address': address,
            'city': city,
            'state': state,
            'zipcode': zipcode,
            'supplier_id': supplier._id,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Warehouse data found`, data: result, title: `View ${name} warehouse detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const warehouseData = await this.findData(id, res);
        const { _id, name, owner_name, phone, email, address, city, state, zipcode, supplier, updated_by, status } = warehouseData;
        const result = {
            'id': _id,
            'name': name,
            'owner_name': owner_name,
            'phone': phone,
            'email': email,
            'address': address,
            'city': city,
            'state': state,
            'zipcode': zipcode,
            'supplier_id': supplier._id,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Warehouse data found`, data: result, title: `Edit ${name} warehouse detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const warehouse = await this.findData(id, res);
        if (!warehouse) return res.status(404).json({ message: `Warehouse not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (warehouse may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const result = await Warehouse.updateOne({ _id: warehouse._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedWarehouse = await this.findData(id, res);
            const { _id, name, owner_name, phone, email, address, city, state, zipcode, supplier } = updatedWarehouse;
            const warehouseData = {
                'id': _id,
                'name': name,
                'owner_name': owner_name,
                'phone': phone,
                'email': email,
                'address': address,
                'city': city,
                'state': state,
                'zipcode': zipcode,
                'supplier': supplier,
            }
            return res.status(200).json({ message: `Warehouse details updated successfully`, data: warehouseData });
        }
        res.status(404).json({ message: `Warehouse not found or no details to update`, data: [] });
    } catch (err) {
        ``
        next(err)
    }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getWarehouse = await Warehouse.findById(id).select('_id').where('status').equals(!status_active);
        if (!getWarehouse) return res.status(404).json({ message: 'Warehouse not found' });

        // const warehouseData = await Warehouse.deleteOne({ _id: id });
        // if (warehouseData.deletedCount === 1) {

        const warehouseData = await Warehouse.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (warehouseData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'owner_name': 'Strng',
                    'phone': 'Number',
                    'email': 'String',
                    'address': 'String',
                    'city': 'String',
                    'state': 'String',
                    'zipcode': 'Number',
                    'supplier': 'SchemaID'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Warehouse not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Warehouse.findById(identifier): Warehouse.findOne({ slug: identifier });
    
    const warehouseData = await query
        .select('_id name owner_name phone email address city state zipcode status supplier updated_by slug')
        .populate('supplier', '_id')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!warehouseData) return res.status(404).json({ message: `Warehouse not found` });
    return warehouseData;
}