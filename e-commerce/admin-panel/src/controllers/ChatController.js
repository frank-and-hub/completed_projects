const mongoose = require('mongoose');

const Message = require('../models/message');
const User = require('../models/user');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

const constName = 'message/';

exports.index = async (req, res, next) => {
    try {
        const userId = req?.userData?.id;

        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = {
            $or: [
                { sender_id: userId },
                { receiver_id: userId }
            ]
        };

        const skip = (page - 1) * limit;
        const totalCount = await Message.countDocuments({
            ...filter
        });

        const query = Message.find(filter)
            .select('_id sender_id receiver_id message')
            .populate('sender_id', '_id name')
            .populate('receiver_id', '_id name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }
        const chats = await query;

        if (chats.length === 0) return res.status(200).json({ message: `No chat found`, data: [] });

        const chatPromises = chats.map(async (chat) => {
            const { _id, sender_id, receiver_id, message, status } = chat;
            return {
                'id': _id,
                'sender_id': sender_id,
                'receiver_id': receiver_id,
                'message': message,
                'status': status
            }
        });
        const chatResponses = await Promise.all(chatPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: chatResponses
            }, title: 'Chat'
        });
    } catch (err) { next(err)  }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create chat form`,
            body: {
                'sender_id': 'schemaId',
                'receiver_id': 'schemaId',
                'message': 'String'
            },
            title: 'Add chat'
        });
    } catch (err) { next(err)  }
}

exports.store = async (req, res, next) => {
    const { receiver_id, message: messages } = req.body;
    try {
        const userId = req?.userData?.id;

        const receiverUser = await User.findById(receiver_id).select('_id name').where('status').equals(status_active);
        if (!receiverUser) return res.status(404).json({ message: `Support not found` });

        if (receiver_id === userId) return res.status(404).json({ message: `You can't text your-self` });

        const message = new Message({
            _id: new mongoose.Types.ObjectId(),
            sender_id: userId,
            receiver_id: receiverUser?._id,
            message: messages,
        });

        const newData = await message.save();
        const response = {
            'id': newData?._id,
            'sender_id': newData?.sender_id,
            'receiver_id': newData?.receiver_id,
            'message': newData?.message
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err)  }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const userId = req?.userData?.id;

        const receiverUser = await User.findById(id).select('_id name');
        if (!receiverUser) return res.status(404).json({ message: `Previous chat not found` });

        if (!mongoose.Types.ObjectId.isValid(receiverUser?._id) || !mongoose.Types.ObjectId.isValid(userId)) {
            return res.status(400).json({ message: 'Invalid user IDs' });
        }

        const query = {
            $or: [
                { sender_id: id, receiver_id: userId },
                { receiver_id: id, sender_id: userId },
            ],
        };

        // fnd messages between login user and a receiver
        const messageData = await Message.find(query)
            .select('_id sender_id receiver_id message updated_by status')
            .populate('sender_id', '_id name')
            .populate('receiver_id', '_id name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (!messageData || messageData?.length === 0) return res.status(200).json({ message: `Chat not found` });

        const chatPromises = messageData.map(async (chat) => {
            const { _id, sender_id, receiver_id, message, status } = chat;
            return {
                'id': _id,
                'sender': sender_id,
                'receiver': receiver_id,
                'message': message,
                'status': status
            }
        });

        const chatResponses = await Promise.all(chatPromises);
        res.status(200).json({ message: `Message data found`, data: chatResponses, title: `View chat detail` });
    } catch (err) { next(err)  }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const userId = req?.userData?.id;

        const receiverUser = await User.findById(id).select('_id name');
        if (!receiverUser) return res.status(404).json({ message: `Chat not found` });

        // fnd messages between login user and a receiver
        const messageData = await Message.findOne({ sender_id: userId, receiver_id: receiverUser?._id })
            .select('_id sender_id receiver_id message')
            .populate('sender_id', '_id name')
            .populate('receiver_id', '_id name');

        if (!messageData) return res.status(404).json({ message: `Message not found` });

        const { _id, sender_id, receiver_id, message } = messageData;
        const result = {
            'id': _id,
            'sender': sender_id,
            'receiver': receiver_id,
            'message': message,
        }
        res.status(200).json({ message: `Message data found`, data: result, title: `Edit ${sender?.name} message detail` });
    } catch (err) { next(err)  }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;

    try {

        // wtch message s gong to be updated
        const messageData = await Message.findById(id).select('_id');
        if (!messageData) return res.status(404).json({ message: `Message not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (message may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const result = await Message.updateOne({ _id: id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedMessage = await Message.findById(id).select('_id name email message');
            const { _id, sender_id, receiver_id, message } = updatedMessage;
            const messageResponse = {
                'id': _id,
                'sender': sender_id,
                'receiver': receiver_id,
                'message': message,
            }
            return res.status(200).json({ message: `Message details updated successfully`, data: messageResponse });
        }
        res.status(404).json({ message: `Message not found or no details to update`, data: [] });
    } catch (err) {
        ``
        next(err)
    }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {

        const getChat = await Message.findById(id);
        if (!getChat) return res.status(404).json({ message: 'Chat not found' });

        // const chatData = await Message.deleteOne({ _id: id });
        // if (chatData.deletedCount === 1) {

        const chatData = await Message.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (aboutDetailData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'sender_id': 'schemaId',
                    'receiver_id': 'schemaId',
                    'message': 'String'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Message not found` });
    } catch (err) { next(err)  }
}

exports.destroy_chat = async (req, res, next) => {
    const { id } = req.params;
    try {
        const userId = req?.userData?.id;

        const receiverUser = await User.findById(id).select('_id name');
        if (!receiverUser) return res.status(404).json({ message: `Receiver not found` });

        const getChat = await Message.find({
            $or: [
                { sender_id: userId, receiver_id: id },
                { sender_id: id, receiver_id: userId }
            ]
        });

        if (getChat.length === 0) {
            return res.status(404).json({ message: 'Chat not found' });
        }

        const chatData = await Message.deleteMany({
            $or: [
                { sender_id: userId, receiver_id: id },
                { sender_id: id, receiver_id: userId }
            ]
        });

        if (chatData.deletedCount === 1) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'sender_id': 'schemaId',
                    'receiver_id': 'schemaId',
                    'message': 'String'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Message not found` });
    } catch (err) { next(err)  }
}