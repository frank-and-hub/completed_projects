const mongoose = require('mongoose');

const User = require('../models/user');
const NewsLetter = require('../models/news-letter');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

const constName = 'news-letter/';

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
                { email: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await NewsLetter.countDocuments({
            ...filter
        });

        const query = NewsLetter.find(filter)
            .select('_id email ip_address updated_by status')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }
        const newsLetters = await query;

        if (newsLetters.length === 0) return res.status(200).json({ message: `No news-letters found`, data: [] });

        const newsLetterPromises = newsLetters.map(async (newsLetter) => {
            const { _id, email, ip_address, updated_by, status } = newsLetter
            return {
                'id': _id,
                'email': email,
                'ip_address': ip_address,
                'updated_by': updated_by,
                'status': status
            }
        });
        const newsLetterResponses = await Promise.all(newsLetterPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: newsLetterResponses
            }, title: 'News Letter'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create newsLetter form`,
            body: {
                'email': 'String',
            },
            title: 'Add news letter'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { email } = req.body;
    try {

        let ip = req.headers['x-forwarded-for']?.split(',')[0] || req.socket.remoteAddress;
        if (ip.includes("::ffff:")) {
            ip = ip.split("::ffff:")[1];
        }

        const existsNewsLetter = await NewsLetter.findOne({ email: email, status: status_active });
        if (existsNewsLetter) return res.status(200).json({ message: 'News letter already exists' });

        const newsLetter = new NewsLetter({
            _id: new mongoose.Types.ObjectId(),
            email,
            ip_address: ip,
        });
        const newData = await newsLetter.save();
        const response = {
            'id': newData?._id,
            'email': newData?.email,
            'ip_address': newData?.ip_address
        }
        res.status(201).json({ message: `Your email is received Successfully`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const newsLetterData = await this.findData(id, res);
        const { _id, email, ip_address, updated_by, status } = newsLetterData;
        const result = {
            'id': _id,
            'email': email,
            'status': status,
            'ip_address': ip_address,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `News letter data found`, data: result, title: `View ${email} news letter detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const newsLetterData = await this.findData(id, res);
        const { _id, email, ip_address, updated_by, status } = newsLetterData;
        const result = {
            'id': _id,
            'email': email,
            'status': status,
            'ip_address': ip_address,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `News letter data found`, data: result, title: `Edit ${email} news letter detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const newsLetter = await this.findData(id, res);
        if (!newsLetter) return res.status(404).json({ message: `News letter not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (news letter may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const result = await NewsLetter.updateOne({ _id: newsLetter._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedNewsLetter = await this.findData(id, res);
            const { _id, email, ip_address } = updatedNewsLetter;
            const newsLetterData = {
                'id': _id,
                'email': email,
                'ip_address': ip_address
            }
            return res.status(200).json({ message: `News letter details updated successfully`, data: newsLetterData });
        }
        res.status(404).json({ message: `News letter not found or no details to update`, data: [] });
    } catch (err) {
        ``
        next(err)
    }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getNewsLetter = await NewsLetter.findById(id).select('_id').where('status').equals(!status_active);
        if (!getNewsLetter) return res.status(404).json({ message: 'NewsLetter not found' });

        // const newsLetterData = await NewsLetter.deleteOne({ _id: id });
        // if (newsLetterData.deletedCount === 1) {

        const newsLetterData = await NewsLetter.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (newsLetterData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'email': 'String',
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `NewsLetter not found` });
    } catch (err) { next(err) }
}

exports.findData = async (id, res) => {

    const newsLetterData = await NewsLetter.findById(id)
        .select('_id email ip_address status updated_by')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!newsLetterData) return res.status(404).json({ message: `NewsLetter not found` });
    return newsLetterData;
}