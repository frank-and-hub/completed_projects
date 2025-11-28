const mongoose = require('mongoose');

const Tag = require('../models/tag');
const User = require('../models/user');
const File = require('../models/file');
const Color = require('../models/color');
const Unit = require('../models/unit');
const Brand = require('../models/brand');
const Product = require('../models/product');
const Warranty = require('../models/warranty');
const Discount = require('../models/discount');
const Category = require('../models/category');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const apiBaseUrl = `${url.apiBaseUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'products/';

exports.index = async (req, res, next) => {
    try {

        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
        const { user_id, status, type, search } = req.query;

        if (user_id) filter.user = user_id;
        if (type) filter.type = type;
        if (status) filter.status = status;

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                { name: { $regex: trimmedSearch, $options: "i" } },
                { slug: { $regex: trimmedSearch, $options: "i" } },
                { url: { $regex: trimmedSearch, $options: "i" } },
                { code: { $regex: trimmedSearch, $options: "i" } },
                { description: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Product.countDocuments({
            ...filter
        });

        const query = Product.find(filter)
            .select('_id name description code url image user type manufactured_date expiry_date status updated_by slug')
            .populate('image', '_id name path')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const products = await query;

        if (products.length === 0) return res.status(200).json({ message: `No products found`, data: [] });

        const productPromises = products.map(async (product) => {
            const { _id, name, description, code, url, image, user, type, manufactured_date, expiry_date, status, slug} = product;
            return {
                'id': _id,
                'name': name,
                'url': url,
                'user': user,
                'type': type,
                'manufactured_date': manufactured_date,
                'expiry_date': expiry_date,
                'description': description,
                'code': code,
                'image': image,
                'status': status,
                'slug': slug
            }
        });

        const productResponses = await Promise.all(productPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: productResponses
            }, title: 'Product'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create product form`,
            body: {
                'name': 'String',
                'description': 'String',
                'code': 'String',
                'specification': 'String',
                'price': 'Number',
                'quantity': 'Number',
                'image': 'file',
                'manufactured_date': 'Date',
                'expiry_date': 'Date',
                'user': 'SchemaId',
                'discount': 'SchemaId',
                'unit': 'SchemaId',
                'brand': 'SchemaId',
                'warranty':'SchemaId',
                'tags': 'array of SchemaID',
                'colors': 'array of SchemaID',
                'categories': 'array of SchemaID',
                'product_images': 'array of SchemaID'
            },
            title: 'Add product'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, description, code, specification, price, quantity, discount_id, brand_id, unit_id, warranty_id, tags, categories, colors, manufactured_date, expiry_date } = req.body;
    const file = req.file;
    try {
        const userId = req?.userData?.id;
        const existingProduct = await Product.findOne({ name, description, specification, discount_id, brand_id });
        if (existingProduct) return res.status(409).json({ message: `Product already exists` });

        const user = await User.findById(userId).select('_id name').where('status').equals(status_active);
        if (!user) return res.status(404).json({ message: `User not found` });

        const discount = await Discount.findById(discount_id).select('_id name').where('status').equals(status_active);
        if (!user) return res.status(404).json({ message: `Discount not found` });

        const brand = await Brand.findById(brand_id).select('_id name').where('status').equals(status_active);
        if (!brand) return res.status(404).json({ message: `Brand not found` });

        const unit = await Unit.findById(unit_id).select('_id name').where('status').equals(status_active);
        if (!unit) return res.status(404).json({ message: `Unit not found` });

        const warranty = await Warranty.findById(warranty_id).select('_id name').where('status').equals(status_active);
        if (!warranty) return res.status(404).json({ message: `warranty not found` });

        const foundColors = await Color.find({ _id: { $in: colors }, status: status_active }).select('_id name');
        if (foundColors.length !== colors.length) return res.status(404).json({ message: 'One or more active colors not found' });

        const foundTags = await Tag.find({ _id: { $in: tags }, status: status_active }).select('_id name');
        if (foundTags.length !== tags.length) return res.status(404).json({ message: 'One or more active tags not found' });

        const foundCategories = await Category.find({ _id: { $in: categories }, status: status_active }).select('_id name');
        if (foundCategories.length !== categories.length) return res.status(404).json({ message: 'One or more active categories not found' });

        // if (!file) return res.status(400).json({ message: `No file uploaded` });

        let savedFile = {};

        if (file) {
            const newFile = new File({
                _id: new mongoose.Types.ObjectId(),
                name: file.filename,
                path: `${apiBaseUrl}/file/${file.filename}`,
                slug: helper.makeSlug(file.filename)
            });
            savedFile = await newFile.save();
        }

        const newProduct = new Product({
            _id: new mongoose.Types.ObjectId(),
            name, description, code, specification, price, quantity, manufactured_date, expiry_date,
            discount: discount._id,
            brand: brand._id,
            unit: unit._id,
            warranty: warranty._id,
            tags: foundTags.map(p => p._id),
            colors: foundColors.map(p => p._id),
            categories: foundCategories.map(p => p._id),
            image: savedFile?._id ?? null,
            user: user._id,
        });
        const newData = await newProduct.save();
        const response = {
            'id': newData._id,
            'name': newData.name,
            'description': newData.description,
            'code': newData.code,
            'manufactured_date': newData.manufactured_date,
            'expiry_date': newData.expiry_date,
            'brand': brand.name,
            'discount': discount.name,
            'unit': unit.name,
            'user': user.name,
            'warranty': warranty.name,
            'image': savedFile?.path ?? null,
            'tags': foundTags.map(perm => ({
                'id': perm._id,
                'name': perm.name
            })),
            'colors': foundColors.map(perm => ({
                'id': perm._id,
                'name': perm.name
            })),
            'categories': foundCategories.map(perm => ({
                'id': perm._id,
                'name': perm.name
            }))
        }
        res.status(201).json({ message: `Product created successfully`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const productData = await this.findData(id, res);
        const { _id, name, description, code, specification, price, quantity, discount, unit, brand, tags, categories, colors, image, product_images, user, warranty, updated_by, manufactured_date, expiry_date, status } = productData;
        const result = {
            'id': _id,
            'name': name,
            'description': description,
            'code': code,
            'manufactured_date': manufactured_date,
            'expiry_date': expiry_date,
            'specification': specification,
            'price': price,
            'quantity': quantity,
            'status': status,
            'discount_id': discount?._id,
            'unit_id': unit?._id,
            'brand_id': brand?._id,
            'warranty_id': warranty?._id,
            'image': image,
            'user': user,
            'updated_by': updated_by,
            'tags': await helper.filterData(tags),
            'colors': await helper.filterData(colors),
            'categories': await helper.filterData(categories),
            'product_images': await helper.filterData(product_images),
        }
        res.status(200).json({ message: `Product data found`, data: result, title: `View ${name} product detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const productData = await this.findData(id, res);
        const { _id, name, description, code, specification, price, quantity, discount, unit, brand, tags, categories, colors, image, product_images, user, warranty, manufactured_date, expiry_date, updated_by, status } = productData;
        const result = {
            'id': _id,
            'name': name,
            'description': description,
            'code': code,
            'manufactured_date': manufactured_date,
            'expiry_date': expiry_date,
            'specification': specification,
            'price': price,
            'quantity': quantity,
            'status': status,
            'discount_id': discount?._id,
            'unit_id': unit?._id,
            'brand_id': brand?._id,
            'warranty_id': warranty?._id,
            'image': image,
            'user': user,
            'updated_by': updated_by,
            'tags': await helper.filterData(tags),
            'colors': await helper.filterData(colors),
            'categories': await helper.filterData(categories),
            'product_images': await helper.filterData(product_images),
        }
        res.status(200).json({ message: `Product data found`, data: result, title: `Edit ${name} product detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const product = await this.findData(id, res);
        if (!product) return res.status(404).json({ message: `Product not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (product may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        if (updateOps['user']) {
            const userData = await User.findById(updateOps['user']).where('status').equals(status_active);
            if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });
        }

        const colorsId = updateOps['colors'];
        const colors = await Color.find({ _id: { $in: colorsId } }).select('_id name');
        if (colorsId && colors?.length !== colorsId?.length) return res.status(404).json({ message: 'One or more colors not found' });

        const tagsId = updateOps['tags'];
        const tags = await Tag.find({ _id: { $in: tagsId } }).select('_id name');
        if (tagsId && tags?.length !== tagsId?.length) return res.status(404).json({ message: 'One or more tags not found' });

        const categoriesId = updateOps['categories'];
        const categories = await Category.find({ _id: { $in: categoriesId } }).select('_id name');
        if (categoriesId && categories?.length !== categoriesId?.length) return res.status(404).json({ message: 'One or more categories not found' });

        const productImagesId = updateOps['product_images'];
        const productImages = await File.find({ _id: { $in: productImagesId } }).select('_id name path');
        if (productImagesId && productImages?.length !== productImagesId?.length) return res.status(404).json({ message: 'One or more product images not found' });

        const result = await Product.updateOne({ _id: product._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedProduct = await this.findData(id, res);
            const { _id, name, description, code, specification, price, quantity, discount, unit, brand, tags, categories, colors, image, product_images, user, warranty, manufactured_date, expiry_date, updated_by, status } = updatedProduct;
            const response = {
                'id': _id,
                'name': name,
                'description': description,
                'code': code,
                'manufactured_date': manufactured_date,
                'expiry_date': expiry_date,
                'specification': specification,
                'price': price,
                'quantity': quantity,
                'status': status,
                'discount': discount,
                'warranty': warranty,
                'unit': unit,
                'brand': brand,
                'image': image,
                'user': user,
                'updated_by': updated_by,
                'colors': colors.map(per => ({
                    'id': per._id,
                    'name': per.name
                })),
                'tags': tags.map(per => ({
                    'id': per._id,
                    'name': per.name
                })),
                'categories': categories.map(per => ({
                    'id': per._id,
                    'name': per.name
                })),
                'product_images': product_images.map(per => ({
                    'id': per._id,
                    'name': per.name,
                    'path': per.path
                })),
            }
            return res.status(200).json({ message: `Product details updated successfully`, data: response });
        }
        res.status(404).json({ message: `Product not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const product = await Product.findById(id).select('_id').where('status').equals(!status_active);
        if (!product) return res.status(404).json({ message: `Product not found!`, });

        // const productData = await Product.deleteOne({ _id: id });
        // if (productData.deletedCount === 1) {

        const productData = await Product.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (productData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'description': 'String',
                    'code': 'String',
                    'specification': 'String',
                    'price': 'Number',
                    'quantity': 'Number',
                    'image': 'file',
                    'manufactured_date': 'Date',
                    'expiry_date': 'Date',
                    'user': 'SchemaId',
                    'discount': 'SchemaId',
                    'unit': 'SchemaId',
                    'brand': 'SchemaId',
                    'warranty':'SchemaId',
                    'tags': 'array of SchemaID',
                    'colors': 'array of SchemaID',
                    'categories': 'array of SchemaID',
                    'product_images': 'array of SchemaID'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Product not found` });
    } catch (err) { next(err) }
}

exports.image = async (req, res, next) => {
    const { id } = req.params;
    const file = req.file;
    try {
        const getProductData = await this.findData(id, res);
        if (!getProductData) return res.status(404).json({ message: `Product not found` });

        const newFile = new File({
            _id: new mongoose.Types.ObjectId(),
            name: file.filename,
            path: `${apiBaseUrl}/file/${file.filename}`,
        });

        // const savedPath = await resizeImage(req.file.buffer, filename);

        const newData = await newFile.save();
        const result = await Product.updateOne({ _id: getProductData._id }, { $set: { image: newData._id } });
        if (result.modifiedCount > 0) return res.status(200).json({ message: `Product image updated` });
        res.status(404).json({ message: `Product not found or no image to update`, data: [] });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Product.findById(identifier): Product.findOne({ slug: identifier });
    
    const productData = await query
        .select('_id name description specification code price quantity discount unit warranty brand tags categories colors image product_images user manufactured_date expiry_date updated_by status slug')
        .populate('discount', '_id name')
        .populate('unit', '_id name')
        .populate('brand', '_id name')
        .populate('user', '_id name.first_name name.middle_name name.last_name')
        .populate('image', '_id name path')
        .populate('warranty', '_id name')
        .populate({
            path: 'colors',
            select: '_id name'
        })
        .populate({
            path: 'tags',
            select: '_id name'
        })
        .populate({
            path: 'categories',
            select: '_id name'
        })
        .populate({
            path: 'product_images',
            select: '_id name path'
        })
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!productData) return res.status(404).json({ message: `Product not found` });
    return productData;
}