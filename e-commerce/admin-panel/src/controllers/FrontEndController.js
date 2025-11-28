// models
const User = require('../models/user');
// const Plan = require('../models/plan');
// const Service = require('../models/service');
// const Project = require('../models/project');
const About = require('../models/about_detail');
// const Testimonial = require('../models/testimonial');
const SocialDetail = require('../models/social_detail');

// status
const status_active = `${process.env.STATUS_ACTIVE}`;

exports.show = async (req, res, next) => {
    const { id } = req.params; // id is for phone number
    try {
        const userData = await User.findOne({ phone: id })
            .select('_id name email phone gender address city state zipcode role image')
            .where('status').equals(status_active)
            .populate('role', '_id name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name')
            .populate('image', '_id name path');

        const social_details = await SocialDetail.find({ 'user': userData?._id })
            .select('_id name url icon')
            .sort({ _id: -1 });

        const about = await About.findOne({ 'user': userData?._id })
            .select('_id title bio experience  user resume')
            .populate('resume', '_id name path');

        // const services = await Service.find({ 'user': userData?._id })
        //     .select('_id title name description user icon')
        //     .sort({ _id: -1 });

        // const testimonials = await Testimonial.find({ 'user': userData?._id })
        //     .select('_id user name title description')
        //     .sort({ _id: -1 });

        // const projects = await Project.find({ 'user': userData?._id })
        //     .select('_id user name description url image type ')
        //     .populate('image', '_id name path')
        //     .sort({ _id: -1 });

        // const plans = await Plan.find({ 'user': userData?._id })
        //     .select('_id user name description price currency payment_method payment_type')
        //     .populate('user', '_id name.first_name name.middle_name name.last_name')
        //     .sort({ _id: -1 });

        if (!userData) return res.status(404).json({ message: `User not found!`, data: [] });

        const userWithDetails = {
            ...userData.toObject(),
            social_details,
            // plans,
            about,
            services,
            // testimonials,
        };
        res.status(200).json({ message: `User founded`, data: userWithDetails, title: `View ${userData?.name} user detail` });
    } catch (err) { next(err)  }
}