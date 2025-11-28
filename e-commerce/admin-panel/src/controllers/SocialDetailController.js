const mongoose = require('mongoose');

const SocialDetail = require('../models/social_detail');
const User = require('../models/user');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'social-details/';

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
                { url: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;

        const totalCount = await SocialDetail.countDocuments({
            ...filter
        });

        const query = SocialDetail.find(filter)
            .select('_id name url icon updated_by status slug')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const social_details = await query;

        if (social_details.length === 0) return res.status(200).json({ message: `No social details found`, data: [] });

        const socialPromises = social_details.map(async (social) => {
            const { _id, name, url, icon, updated_by, status, slug} = social;
            return {
                'id': _id,
                'name': name,
                'icon': icon,
                'url': url,
                'updated_by': updated_by,
                'status': status,
                'slug': slug
            }
        });
        const socialResponses = await Promise.all(socialPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: socialResponses
            }, title: 'Social Detail'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create social detail form`,
            body: {
                'name': 'String',
                'url': 'Url',
                'icon': 'String'
            },
            title: 'Add social detail'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, url, icon } = req.body;
    try {
        const existsSocialDetail = await SocialDetail.findOne({ name: name, status: status_active });
        if (existsSocialDetail) return res.status(200).json({ message: 'SocialDetail already exists' });

        const social = new SocialDetail({
            _id: new mongoose.Types.ObjectId(),
            name,
            url,
            icon,
        });
        const newData = await social.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'url': newData?.url,
            'icon': newData?.icon,
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const socialData = await this.findData(id, res);
        const { _id, name, url, icon, updated_by, status } = socialData;
        const result = {
            'id': _id,
            'name': name,
            'url': url,
            'icon': icon,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `SocialDetail data found`, data: result, title: `View ${name} social detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const socialData = await this.findData(id, res);
        const { _id, name, url, icon, updated_by, status } = socialData;
        const result = {
            'id': _id,
            'name': name,
            'url': url,
            'icon': icon,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `SocialDetail data found`, data: result, title: `Edit ${name} social detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const social = await this.findData(id, res);
        if (!social) return res.status(404).json({ message: `SocialDetail not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (social may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const result = await SocialDetail.updateOne({ _id: social._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedSocialDetail = await this.findData(id, res);
            const { _id, name, url, icon } = updatedSocialDetail;
            const socialData = {
                'id': _id,
                'name': name,
                'url': url,
                'icon': icon
            }
            return res.status(200).json({ message: `SocialDetail details updated successfully`, data: socialData });
        }
        res.status(404).json({ message: `SocialDetail not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getSocialDetail = await SocialDetail.findById(id).select('_id').where('status').equals(!status_active);
        if (!getSocialDetail) return res.status(404).json({ message: 'SocialDetail not found' });

        // const socialData = await SocialDetail.deleteOne({ _id: id });
        // if (socialData.deletedCount === 1) {

        const socialData = await SocialDetail.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (socialData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'url': 'String',
                    'icon': 'String'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `SocialDetail not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? SocialDetail.findById(identifier): SocialDetail.findOne({ slug: identifier });
    
    const socialData = await query
        .select('_id name url icon updated_by status slug')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!socialData) return res.status(404).json({ message: `SocialDetail not found` });
    return socialData;
}