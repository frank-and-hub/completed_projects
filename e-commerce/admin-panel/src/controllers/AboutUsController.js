const mongoose = require('mongoose');

const AboutUs = require('../models/about_us');
const User = require('../models/user');

const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

// config url
const url = require('../config/url');;

// base url
const status_active = `${process.env.STATUS_ACTIVE}`;

exports.index = async (req, res, next) => {
    try {
        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { ...req?.query?.filter };

        const skip = (page - 1) * limit;
        const totalCount = await AboutUs.countDocuments({
            ...filter
        });

        const query = AboutUs.find()
            .select('_id info updated_by status')
            .where('status').equals(status_active)
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const about_us = await query;

        if (about_us.length === 0) return res.status(200).json({ message: `No about found`, data: [] });

        const aboutUsPromises = about_us.map(async (aboutUs) => {
            const { _id, info, updated_by, status } = aboutUs;
            return {
                'id': _id,
                'info': info,
                'status': status,
                'updated_by': updated_by,
            }
        });
        const aboutUsResponses = await Promise.all(aboutUsPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: aboutUsResponses
            }, title: 'About Us'
        });
    } catch (err) { next(err)  }
}

exports.store = async (req, res, next) => {
    const { info } = req.body;
    try {
        let userId = req?.userData?.id;

        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsAboutUs = await AboutUs.findOne({ info: info, status: status_active });
        if (existsAboutUs) return res.status(200).json({ message: 'About us info already exists' });

        await AboutUs.deleteMany({});

        const aboutUs = new AboutUs({
            _id: new mongoose.Types.ObjectId(),
            info,
            updated_by: userData?._id
        });

        const newAboutUs = await aboutUs.save();

        res.status(201).json({ message: `Successfully updated`, data: newAboutUs[0] });
    } catch (err) { next(err)  }
}
