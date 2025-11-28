const User = require('../models/user');
const Role = require('../models/role');

const { startOfWeek, endOfWeek, startOfMonth, endOfMonth } = require('date-fns');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

exports.index = async (req, res, next) => {
    try {
        const filterData = {};
        const { filter } = req.body;

        const userFilter = await this.applyFilter(filter?.userFilter, 'user');
        const adminFilter = await this.applyFilter(filter?.adminFilter, 'admin' || 'supper-admin');
        const guestFilter = await this.applyFilter(filter?.guestFilter, 'guest-user');
        const contactFilter = await this.applyFilter(filter?.contactFilter);

        const paymentFilter = await this.applyFilter(filter?.paymentFilter);
        const reportFilter = await this.applyFilter(filter?.reportFilter);
        const trafficFilter = await this.applyFilter(filter?.trafficFilter);

        const responses = {
            usersCount: await User.countDocuments(userFilter),
            adminsCount: await User.countDocuments(adminFilter),
            guestsCount: await User.countDocuments(guestFilter),
            paymentAmount: 1000,
            trafficDataChart: [
                { value: 1048, name: 'Search Engine' },
                { value: 735, name: 'Direct' },
                { value: 580, name: 'Email' },
                { value: 484, name: 'Union Ads' },
                { value: 300, name: 'Video Ads' }
            ],
            reportDataChart: [
                { name: 'Sales', data: [31, 40, 28, 51, 42, 82, 56] },
                { name: 'Revenue', data: [11, 32, 45, 32, 34, 52, 41] },
                { name: 'Customers', data: [15, 11, 32, 18, 9, 24, 11] }
            ]
        };

        res.status(200).json({
            message: `List retrieved successfully`, response: {
                data: responses
            }, title: 'Dashboard'
        });
    } catch (err) { next(err)  }
}

exports.applyFilter = async (filter, role) => {
    const filteredData = {};

    if (filter) {
        switch (filter) {
            case 'this_week':
                filteredData.createdAt = { $gte: startOfWeek(new Date()), $lt: endOfWeek(new Date()) };
                break;
            case 'this_month':
                filteredData.createdAt = { $gte: startOfMonth(new Date()), $lt: endOfMonth(new Date()) };
                break;
            case 'this_year':
                filteredData.createdAt = { $gte: new Date(new Date().getFullYear(), 0, 1), $lt: new Date(new Date().getFullYear() + 1, 0, 1) };
                break;
            case 'today':
                filteredData.createdAt = new Date();
                break;
            default:
                filteredData.createdAt = null;
        }
    }

    if (role) {
        const roleData = await Role.findOne({ name: role });
        filteredData.role = roleData?._id;
    }

    return filteredData;
}