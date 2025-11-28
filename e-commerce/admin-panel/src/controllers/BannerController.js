const mongoose = require('mongoose');

const Banner = require('../models/banner');
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

const constName = 'banners/';

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
                { title: { $regex: trimmedSearch, $options: "i" } },
                { description: { $regex: trimmedSearch, $options: "i" } },
            ];
        }
 
        const skip = (page - 1) * limit;
        const totalCount = await Banner.countDocuments({
            ...filter
        });

        const query = Banner.find(filter)
            .select('_id name title description url image user updated_by status slug')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('image', '_id name path')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const banners = await query;

        if (banners.length === 0) return res.status(200).json({ message: `No banners found`, data: [] });

        const bannerPromises = banners.map(async (banner) => {
            const { _id, name, title, description, image, status, url, slug} = banner;
            return {
                'id': _id,
                'name': name,
                'title': title,
                'description': description,
                // 'url': url,
                'image': image,
                'status': status,
                'slug': slug
            }
        });
        const bannerResponses = await Promise.all(bannerPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: bannerResponses
            }, title: 'Banner'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create Banner form`,
            body: {
                'name': 'String',
                'title': 'String',
                'description': 'String',
                'image': 'image|file',
                'url': 'Srting'
            },
            title: 'Add banner'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, title, description, url } = req.body;
    try {
        let userId = req?.userData?.id;
        const file = req.file;

        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsBanner = await Banner.findOne({ name: name, title: title, status: status_active });
        if (existsBanner) return res.status(200).json({ message: 'Banner already exists' });

        console.log(helper.makeSlug(file.filename));
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
        const banner = new Banner({
            _id: new mongoose.Types.ObjectId(),
            user: userData?._id,
            name,
            title,
            url,
            image: newData?._id,
            description: desc.length > 0 ? generateDescription() : description,
            slug: helper.makeSlug(name)
        });
        const newBanner = await banner.save();
        const response = {
            'id': newBanner?._id,
            'user': userData?.name,
            'name': newBanner?.name,
            'title': newBanner?.title,
            'image': newData?.path,
            'url': newData?.url,
            'description': newBanner?.description,
            'slug': newBanner.slug
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const bannerData = await this.findData(id, res);
        const { _id, name, title, description, url, user, image, updated_by, status, slug} = bannerData;
        const result = {
            'id': _id,
            'name': name,
            'title': title,
            'url': url,
            'user': user,
            'description': description,
            'image': image,
            'status': status,
            'slug': slug,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Banner data found`, data: result, title: `View ${name} banner detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const bannerData = await this.findData(id, res);
        const { _id, name, title, description, url, user, image, updated_by, status, slug} = bannerData;
        const result = {
            'id': _id,
            'name': name,
            'title': title,
            'url': url,
            'user': user,
            'description': description,
            'image': image,
            'status': status,
            'slug': slug,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Banner data found`, data: result, title: `Edit ${name} banner detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const file = req.file;

        const banner = await this.findData(id, res);
        if (!banner) return res.status(404).json({ message: `Banner not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (banner may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        if (file) {
            const newFile = new File({
                _id: new mongoose.Types.ObjectId(),
                name: file.filename,
                path: `${apiBaseUrl}/file/${file.filename}`,
            });
            const newData = await newFile.save();
            if (newData?._id) {
                updateOps['image'] = newData._id;
            }
        }

        const result = await Banner.updateOne({ _id: banner._id }, { $set: updateOps });

        if (result.modifiedCount > 0) {
            const updatedBanner = await this.findData(id, res);
            const { _id, name, title, description, user, image, slug} = updatedBanner;

            const bannerData = {
                'id': _id,
                'name': name,
                'title': title,
                'url': url,
                'user': user,
                'image': image,
                'description': description,
                'slug': slug
            }
            return res.status(200).json({ message: `Banner details updated successfully`, data: bannerData });
        }
        res.status(404).json({ message: `Banner not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getBanner = await Banner.findById(id).select('_id').where('status').equals(!status_active);
        if (!getBanner) return res.status(404).json({ message: 'Banner not found' });

        // const bannerData = await Banner.deleteOne({ _id: id });
        // if (bannerData.deletedCount === 1) {

        const bannerData = await Banner.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (bannerData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'title': 'String',
                    'description': 'String',
                    'image': 'image|file',
                    'url': 'String'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Banner not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Banner.findById(identifier): Banner.findOne({ slug: identifier });
    const bannerData = await query.select('_id name title description user image url updated_by status slug')
        .populate('user', '_id name.first_name name.middle_name name.last_name')
        .populate('image', '_id name path')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!bannerData) return res.status(404).json({ message: `Banner not found` });
    return bannerData;
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
        const result = await Banner.updateOne({ _id: getData._id }, { $set: { image: newData._id } });
        if (result.modifiedCount > 0) return res.status(200).json({ message: `image updated` });
        res.status(404).json({ message: `Data not found or no image to update`, data: [] });
    } catch (err) { next(err) }
}