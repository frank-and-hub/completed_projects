const mongoose = require('mongoose');

const ErrorLog = require('../models/errorlog');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'errorlogs/';

exports.index = async (req, res, next) => {
    try {
        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
        const { status, search } = req.query;

        if (status) filter.statusCode = status;

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                { name: { $regex: trimmedSearch, $options: "i" } },
                { route: { $regex: trimmedSearch, $options: "i" } },
                { errorMessage: { $regex: trimmedSearch, $options: "i" } },
                { errorType: { $regex: trimmedSearch, $options: "i" } },
                { IP: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await ErrorLog.countDocuments({
            ...filter
        });

        const query = ErrorLog.find(filter)
            .select('_id route statusCode errorMessage errorType stackTrace IP status');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const errorlogs = await query;
        if (errorlogs.length === 0) return res.status(200).json({ message: `No error logs found`, data: [] });

        const errorlogPromises = errorlogs.map(async (errorlog) => {
            const { _id, route, statusCode, errorMessage, errorType, stackTrace, IP, status } = errorlog;

            return {
                'id': _id,
                'route': route,
                'statusCode': statusCode,
                'errorMessage': errorMessage,
                // 'errorType': errorType,
                'stackTrace': stackTrace,
                // 'ip': IP,
                'status': status
            }
        });
        const errorlogResponses = await Promise.all(errorlogPromises);
        res.status(200).json({
            message: `Error logs list retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: errorlogResponses
            }, title: 'Error log'
        });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const errorlogData = await this.findData(id, res);
        const { _id, route, statusCode, errorMessage, errorType, stackTrace, IP, status } = errorlogData;
        const result = {
            'id': _id,
            'route': route,
            'statusCode': statusCode,
            'errorMessage': errorMessage,
            'errorType': errorType,
            'stackTrace': stackTrace,
            'ip': IP,
            'status': status
        }
        res.status(200).json({ message: `Error log data found`, data: result, title: `View ${route} errorlog detail` });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getErrorLog = await ErrorLog.findById(id).select('_id').where('status').equals(!status_active);
        if (!getErrorLog) return res.status(404).json({ message: 'ErrorLog not found' });

        const errorlogData = await ErrorLog.deleteOne({ _id: id });
        if (errorlogData.deletedCount === 1) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'route': 'String',
                    'statusCode': 'String',
                    'errorMessage': 'String',
                    'errorType': 'String',
                    'stackTrace': 'String',
                    'ip': 'String'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `ErrorLog not found` });
    } catch (err) { next(err) }
}

exports.findData = async (id, res) => {

    const errorlogData = await ErrorLog.findById(id)
        .select('_id route statusCode errorMessage errorType stackTrace IP status');

    if (!errorlogData) return res.status(404).json({ message: `Error log not found` });
    return errorlogData;
}