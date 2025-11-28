const mongoose = require('mongoose');

const Setting = require('../models/setting');
const User = require('../models/user');

// config url
const url = require('../config/url');;

const status_active = `${process.env.STATUS_ACTIVE}`;

exports.store = async (req, res, next) => {
    const { color, background, theme, filter, isSidebarToggled } = req.body;
    try {
        let userId = req?.userData?.id;

        const userData = await User.findActiveById(userId, status_active);
        if (!userData) return res.status(401).json({ message: `User not found!`, data: [] });

        const existsSetting = await Setting.findOne({ user: userData._id });
        if (existsSetting) await Setting.deleteOne({ user: userData._id });

        const setting = new Setting({
            _id: new mongoose.Types.ObjectId(),
            user: userData._id,
            color, background, theme, filter, isSidebarToggled
        });
        const newData = await setting.save();
        const response = {
            'id': newData?._id,
            'color': newData?.color,
            'user': userData?.name,
            'background': newData?.background,
            'theme': newData?.theme,
            'filter': newData?.filter,
            'isSidebarToggled': newData?.isSidebarToggled,
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.index = async (req, res, next) => {
    try {
        let userId = req?.userData?.id;
        const settingData = await this.findData(userId, res);
        if (settingData) {

            const { _id, color, background, user, theme, filter, isSidebarToggled, updated_by } = settingData;
            const result = {
                'id': _id,
                'color': color,
                'user': user,
                'background': background,
                'theme': theme,
                'filter': filter,
                'isSidebarToggled': isSidebarToggled,
                'updated_by': updated_by
            }
            res.status(200).json({ message: `Setting data found`, data: result, title: `View ${user?.name} setting detail` });
        }
        res.status(200).json({ message: `Setting data not found`, data: {}, title: `Create setting detail` });
    } catch (err) { next(err) }
}

exports.findData = async (id, res) => {
    const settingData = await Setting.findOne({ user: id })
        .select('_id color background theme filter user isSidebarToggled updated_by')
        .populate('user', '_id name.first_name name.middle_name name.last_name')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    // if (!settingData) return res.status(404).json({ message: `Setting not found` });
    return settingData;
}