const mongoose = require('mongoose');

const Brand = require('../models/brand');
const User = require('../models/user');
const File = require('../models/file');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const apiBaseUrl = `${url.apiBaseUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

const constName = 'brands/';

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
        const totalCount = await Brand.countDocuments({
            ...filter
        });

        const query = Brand.find(filter)
            .select('_id name description image user updated_by status slug')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('image', '_id name path')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const brands = await query;

        if (brands.length === 0) return res.status(200).json({ message: `No brands found`, data: [] });

        const brandPromises = brands.map(async (brand) => {
            const { _id, name, description, image, status, slug} = brand;
            return {
                'id': _id,
                'name': name,
                'description': description,
                'image': image,
                'status': status,
                'slug': slug
            }
        });
        const brandResponses = await Promise.all(brandPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: brandResponses
            }, title: 'Brand'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create Brand form`,
            body: {
                'name': 'String',
                'image': 'image|file',
                'description': 'String',
            },
            title: 'Add brand'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, description } = req.body;
    try {
        let userId = req?.userData?.id;
        const file = req.file;
        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsBrand = await Brand.findOne({ name: name, status: status_active });
        if (existsBrand) return res.status(200).json({ message: 'Brand already exists' });

        let newData = {};
        if (file) {
            const newFile = new File({
                _id: new mongoose.Types.ObjectId(),
                name: file.filename,
                path: `${apiBaseUrl}/file/${file.filename}`,
                slug: helper.makeSlug(file.filename)
            });
            newData = await newFile.save();
        }

        const brand = new Brand({
            _id: new mongoose.Types.ObjectId(),
            user: userData?._id,
            name,
            image: newData?._id,
            description,
            slug: helper.makeSlug(name)
        });
        const newBrand = await brand.save();
        const response = {
            'id': newBrand?._id,
            'user': userData?.name,
            'name': newBrand?.name,
            'image': newData?.path,
            'description': newBrand?.description,
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const brandData = await this.findData(id, res);
        const { _id, name, description, user, image, updated_by, status } = brandData;
        const result = {
            'id': _id,
            'name': name,
            'user': user,
            'description': description,
            'image': image,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Brand data found`, data: result, title: `View ${name} brand detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const brandData = await this.findData(id, res);
        const { _id, name, description, user, image, updated_by, status } = brandData;
        const result = {
            'id': _id,
            'name': name,
            'user': user,
            'description': description,
            'image': image,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Brand data found`, data: result, title: `Edit ${name} brand detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const file = req.file;
        
        const brand = await this.findData(id, res);
        if (!brand) return res.status(404).json({ message: `Brand not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (brand may not exist or the data is the same)` });
        
        const updateOps = helper.updateOps(req);

        if (file) {
            const newFile = new File({
                _id: new mongoose.Types.ObjectId(),
                name: file.filename,
                path: `${apiBaseUrl}/file/${file.filename}`,
                slug: helper.makeSlug(file.filename)
            });
            const newData = await newFile.save();
            if (newData?._id) {
                updateOps['image'] = newData._id;
            }
        }

        const result = await Brand.updateOne({ _id: brand._id }, { $set: updateOps });

        if (result.modifiedCount > 0) {
            const updatedBrand = await this.findData(id, res);
            const { _id, name, description, user, image } = updatedBrand;

            const brandData = {
                'id': _id,
                'name': name,
                'user': user,
                'image': image,
                'description': description
            }
            return res.status(200).json({ message: `Brand details updated successfully`, data: brandData });
        }
        res.status(404).json({ message: `Brand not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getBrand = await Brand.findById(id).select('_id').where('status').equals(!status_active);
        if (!getBrand) return res.status(404).json({ message: 'Brand not found' });

        // const brandData = await Brand.deleteOne({ _id: id });
        // if (brandData.deletedCount === 1) {

        const brandData = await Brand.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (brandData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'image': 'image|file',
                    'description': 'String'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Brand not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Brand.findById(identifier): Brand.findOne({ slug: identifier });
    
    const brandData = await query.select('_id user name description image slug status updated_by')
        .populate('user', '_id name.first_name name.middle_name name.last_name')
        .populate('image', '_id name path')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');
        
    if (!brandData) return res.status(404).json({ message: `Brand not found` });
    return brandData;
}

exports.image = async (req, res, next) => {
    const { id } = req.params;
    const file = req.file;
    try {
        const getData = await this.findData(id, res);
        if (!getData) return res.status(404).json({ message: `Data not found` });
        const newFile = new File({
            _id: new mongoose.Types.ObjectId(),
            name: file.filename,
            path: `${apiBaseUrl}/file/${file.filename}`,
        });
        // const savedPath = await resizeImage(req.file.buffer, filename);
        const newData = await newFile.save();
        const result = await Brand.updateOne({ _id: getData._id }, { $set: { image: newData._id } });
        if (result.modifiedCount > 0) return res.status(200).json({ message: `image updated` });
        res.status(404).json({ message: `Data not found or no image to update`, data: [] });
    } catch (err) { next(err) }
}
