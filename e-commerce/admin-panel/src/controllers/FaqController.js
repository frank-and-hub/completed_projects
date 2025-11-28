const mongoose = require('mongoose');

const Faq = require('../models/faq');
const User = require('../models/user');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'faq/';

exports.index = async (req, res, next) => {
    try {

        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { ...req?.query?.filter };

        const skip = (page - 1) * limit;
        const totalCount = await Faq.countDocuments({
            ...filter
        });

        const query = Faq.find(filter)
            .select('_id question answer created_by status')
            .where('status').equals(status_active)
            .populate('created_by', '_id name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }
        const faq = await query;

        if (faq.length === 0) return res.status(200).json({ message: `No faq found`, data: [] });

        const faqPromises = faq.map(async (faq) => {
            const { _id, question, answer, created_by, status } = faq;
            return {
                'id': _id,
                'question': question,
                'answer': answer,
                'created_by': created_by,
                'status': status
            }
        });
        const faqResponses = await Promise.all(faqPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: faqResponses
            }, title: ' Frequently Asked Questions'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create faq form`,
            body: {
                'question': 'String',
                'answer': 'String'
            },
            title: 'Add faq'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { question, answer } = req.body;
    try {

        let userId = req?.userData?.id;

        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsFaq = await Faq.findOne({ question: question, status: status_active });
        if (existsFaq) return res.status(200).json({ message: 'Faq already exists' });

        const faq = new Faq({
            _id: new mongoose.Types.ObjectId(),
            created_by: userData?._id,
            question,
            answer
        });

        const newData = await faq.save();
        const response = {
            'id': newData?._id,
            'question': newData?.question,
            'answer': newData?.answer,
            'created_by': newData?.created_by
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const faqData = await this.findData(id);
        const { _id, question, answer, created_by, status } = faqData;
        const result = {
            'id': _id,
            'question': question,
            'answer': answer,
            'created_by': created_by,
            'status': status
        }
        res.status(200).json({ message: `Faq data found`, data: result, title: `View faq detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const faqData = await this.findData(id);
        const { _id, question, answer, created_by, status } = faqData;
        const result = {
            'id': _id,
            'question': question,
            'answer': answer,
            'created_by': created_by,
            'status': status
        }
        res.status(200).json({ message: `Faq data found`, data: result, title: `Edit faq detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const faq = await this.findData(id, res);
        if (!faq) return res.status(404).json({ message: `Faq not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (faq may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const result = await Faq.updateOne({ _id: faq._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedFaq = await this.findData(id, res);
            const { _id, question, answer, created_by } = updatedFaq;
            const response = {
                'id': _id,
                'question': question,
                'answer': answer,
                'created_by': created_by
            }
            return res.status(200).json({ message: `Faq details updated successfully`, data: response });
        }
        res.status(404).json({ message: `Faq not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getFaq = await Faq.findById(id).select('_id').where('status').equals(!status_active);
        if (!getFaq) return res.status(404).json({ message: 'Faq not found' });

        // const faqData = await Faq.deleteOne({ _id: id });
        // if (faqData.deletedCount === 1) {

        const faqData = await Faq.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (faqData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'question': 'String',
                    'answer': 'String',
                    'userId': 'ID',
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Faq not found` });
    } catch (err) { next(err) }
}

exports.findData = async (id, res) => {
   
    const faqData = await Faq.findById(id)
        .select('_id question answer created_by status')
        .populate('created_by', '_id name');

    if (!faqData) return res.status(404).json({ message: `Faq not found` });
    return faqData;
}