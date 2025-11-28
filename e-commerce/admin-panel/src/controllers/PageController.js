const mongoose = require('mongoose');

const PageDetail = require('../models/page');
const User = require('../models/user');

const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

// helper
const helper = require('../utils/helper');;

// base url
const status_active = `${process.env.STATUS_ACTIVE}`;

exports.index = async (req, res, next) => {
    const { type } = req.query;
    try {
        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { ...req?.query?.filter };

        if (type) filter.type = type;

        const skip = (page - 1) * limit;
        const totalCount = await PageDetail.countDocuments({
            ...filter
        });

        const query = PageDetail.find(filter)
            .select('_id type description updated_by status')
            .where('status').equals(status_active)
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const page_detail = await query;

        if (page_detail.length === 0) return res.status(200).json({ message: `No page found`, data: [] });

        const pageDetailPromises = page_detail.map(async (pageDetail) => {
            const { _id, type, description, updated_by, status } = pageDetail;
            return {
                'id': _id,
                'type': type,
                'description': description,
                'status': status,
                'updated_by': updated_by,
            }
        });
        const pageDetailResponses = await Promise.all(pageDetailPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: pageDetailResponses
            }, title: type
        });
    } catch (err) { next(err)  }
}

exports.store_or_update = async (req, res, next) => {
    const { type, description } = req.body;
    try {
        const userId = req?.userData?.id;

        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsPageDetail = await PageDetail.findOne({ type: type, status: status_active });
        console.log(existsPageDetail);
        if (existsPageDetail) {
            const updateOps = {
                'type' : type,
                'description' : description,
            };
            const result = await PageDetail.updateOne(
                { _id: existsPageDetail._id },
                { $set: updateOps }
            );
            if (result.modifiedCount > 0) {
                res.status(201).json({ message: `Successfully updated`, data: updateOps });
            } else {
                res.status(200).json({ message: `No changes made`, data: updateOps });
            }
        } else {

            const pageDetail = new PageDetail({
                _id: new mongoose.Types.ObjectId(),
                type,
                description,
                updated_by: userData?._id,
                status: status_active
            });

            const newPageDetail = await pageDetail.save();
            res.status(201).json({ message: `Successfully created`, data: newPageDetail });
        }

    } catch (err) { next(err) }
};

