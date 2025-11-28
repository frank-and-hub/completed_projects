const mongoose = require('mongoose');

const File = require('../models/file');
const AboutDetail = require('../models/about_detail');
const helper = require('../utils/helper');

// config url
const url = require('../config/url');

// base url
const apiBaseUrl = `${url.apiBaseUrl}`;

exports.index = async (req, res, next) => {
    const userId = req?.userData?.id;
    try {
        const aboutDetails = await AboutDetail.find({ user: userId })
            .select('_id user title bio experience resume updated_by')
            .populate('user', '_id name.first_name name.middle_name name.last_name')
            .populate('resume', '_id name path')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (aboutDetails.length === 0) return res.status(200).json({ message: `No aboutDetails found`, data: [] });

        const { _id, user, title, bio, experience, resume, updated_by } = aboutDetails[0];

        res.status(200).json({
            message: `data retrieved successfully`, response: {
                data: {
                    'id': _id,
                    'user': user,
                    'updated_by': updated_by,
                    'title': title,
                    'bio': bio,
                    'experience': experience,
                    'resume': resume,
                }
            }
        });
    } catch (err) { next(err)  }
}

exports.update = async (req, res, next) => {
    const { title, bio, experience } = req.body;
    const userId = req?.userData?.id;
    const file = req.file;
    try {

        let newFileData = null;
        if (file) {
            const newFile = new File({
                _id: new mongoose.Types.ObjectId(),
                name: file.filename,
                path: `${apiBaseUrl}/file/${file.filename}`,
                slug: helper.makeSlug(file.filename)
            });
            newFileData = await newFile.save();
        }

        await AboutDetail.deleteOne({ user: userId });

        const aboutDetail = new AboutDetail({
            _id: new mongoose.Types.ObjectId(),
            user: userId,
            title: title,
            bio: bio,
            experience: experience,
            resume: newFileData?._id
        });

        const newData = await aboutDetail.save();
        const response = {
            id: newData._id,
            user: newData.user,
            title: newData.title,
            bio: newData.bio,
            experience: newData.experience,
            resume: newFileData?.path
        }
        return res.status(201).json({ message: 'About details updated successfully', data: response });
    } catch (err) { next(err)  }
}