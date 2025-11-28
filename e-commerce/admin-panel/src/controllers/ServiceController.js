const mongoose = require('mongoose');

const Service = require('../models/service');
const User = require('../models/user');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'services/';

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
                { description: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Service.countDocuments({
            ...filter
        });

        const query = Service.find(filter)
            .select('_id name icon description user status updated_by slug')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const services = await query;

        if (services.length === 0) return res.status(200).json({ message: `No services found`, data: [] });

        const servicePromises = services.map(async (service) => {
            const { _id, name, icon, description, status, user, slug} = service;
            return {
                'id': _id,
                'name': name,
                'icon': icon,
                'description': description,
                'user': user,
                'status': status,
                'slug':slug
            }
        });
        const serviceResponses = await Promise.all(servicePromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: serviceResponses
            }, title: 'Service'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create service form`,
            body: {
                'name': 'String',
                'description': 'String',
                'icon': 'String',
                'userId': 'SchemaId'
            },
            title: 'Add service'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, description, icon } = req.body;
    try {
        let userId = req?.userData?.id;

        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsService = await Service.findOne({ name: name, status: status_active, user: userData._id });
        if (existsService) return res.status(200).json({ message: 'Service already exists' });

        const service = new Service({
            _id: new mongoose.Types.ObjectId(),
            user: userData._id,
            name,
            description,
            icon
        });
        const newData = await service.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'icon': newData?.icon,
            'description': newData?.description,
            'user': userData?.name
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const serviceData = await this.findData(id, res);
        const { _id, name, icon, description, user, updated_by, status } = serviceData;
        const result = {
            'id': _id,
            'name': name,
            'icon': icon,
            'description': description,
            'user': user,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Service data found`, data: result, title: `View ${name} service detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const serviceData = await this.findData(id, res);
        const { _id, name, icon, description, user } = serviceData;
        const result = {
            'id': _id,
            'name': name,
            'icon': icon,
            'description': description,
            'user': user?._id
        }
        res.status(200).json({ message: `Service data found`, data: result, title: `Edit ${name} service detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const service = await this.findData(id, res);
        if (!service) return res.status(404).json({ message: `Service not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (service may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        if (updateOps['user']) {
            const userData = await User.findById(updateOps['user']).select('_id').where('status').equals(status_active);
            if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });
        }

        const result = await Service.updateOne({ _id: service._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedService = await this.findData(id, res);
            const { _id, name, description, icon, user } = updatedService;
            const response = {
                'id': _id,
                'name': name,
                'description': description,
                'icon': icon,
                'user': user
            }
            return res.status(200).json({ message: `Service details updated successfully`, data: response });
        }
        res.status(404).json({ message: `Service not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getService = await Service.findById(id).select('_id').where('status').equals(!status_active);
        if (!getService) return res.status(404).json({ message: 'Service not found' });

        // const serviceData = await Service.deleteOne({ _id: id });
        // if (serviceData.deletedCount === 1) {

        const serviceData = await Service.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (serviceData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'description': 'String',
                    'icon': 'String',
                    'user': 'ID',
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Service not found` });
    } catch (err) { next(err) }
}

exports.user_services = async (req, res, next) => {
    const { userId } = req.params;
    try {
        const getUserData = await User.findActiveById(userId, status_active);
        if (!getUserData) return res.status(404).json({ message: `User not found` });

        const getServices = await Service.find({ user: getUserData._id, status: status_active });
        if (getServices.length === 0) return res.status(200).json({ message: `No services found`, data: [] });

        const getServicesPromises = getServices.map(async (services) => {
            const { _id, name, description, icon } = services;

            return {
                'id': _id,
                'name': name,
                'description': description,
                'icon': icon,
                'request': { method: 'GET', url: `${baseurl}${constName}${_id}` }
            }
        });
        const getServicesResponses = await Promise.all(getServicesPromises);
        res.status(200).json({ message: `List User-Services`, response: { count: getServices.length, data: getServicesResponses } });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Service.findById(identifier): Service.findOne({ slug: identifier });
    
    const serviceData = await query
        .select('_id name icon description user updated_by status slug')
        .populate('user', '_id name.first_name name.middle_name name.last_name')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!serviceData) return res.status(404).json({ message: `Service not found` });
    return serviceData;
}