const mongoose = require('mongoose');

const TermsAndConditions = require('../models/terms_and_conditions');
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
        const totalCount = await TermsAndConditions.countDocuments({
            ...filter
        });

        const query = TermsAndConditions.find()
            .select('_id t_and_c updated_by status')
            .where('status').equals(status_active)
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const terms_and_conditions = await query;

        if (terms_and_conditions.length === 0) return res.status(200).json({ message: `No terms and conditions found`, data: [] });

        const termsAndConditionsPromises = terms_and_conditions.map(async (termsAndConditions) => {
            const { _id, t_and_c, updated_by, status } = termsAndConditions;
            return {
                'id': _id,
                't_and_c': t_and_c,
                'status': status,
                'updated_by': updated_by,
            }
        });
        const termsAndConditionsResponses = await Promise.all(termsAndConditionsPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: termsAndConditionsResponses
            }, title: 'Terms And Conditions'
        });
    } catch (err) { next(err)  }
}

exports.store = async (req, res, next) => {
    const { t_and_c } = req.body;
    try {
        let userId = req?.userData?.id;

        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsTermsAndConditions = await TermsAndConditions.findOne({ t_and_c: t_and_c, status: status_active });
        if (existsTermsAndConditions) return res.status(200).json({ message: 'Terms nd conditions already exists' });

        let deleteData = await TermsAndConditions.deleteMany({});

        const termsAndConditions = new TermsAndConditions({
            _id: new mongoose.Types.ObjectId(),
            t_and_c,
            updated_by: userData?._id
        });

        const newTermsAndConditions = await termsAndConditions.save();

        res.status(201).json({ message: `Successfully updated`, data: newTermsAndConditions[0] });
    } catch (err) { next(err)  }
}
