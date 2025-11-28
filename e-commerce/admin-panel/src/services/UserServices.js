const User = require(`@/models/user`);
const { phoneFormate } = require(`@/utils/helper`);
const { hashPassword } = require(`@/services/AuthServices`);
const { default: mongoose } = require("mongoose");
const status_active = `${process.env.STATUS_ACTIVE}`;

class UserService {
    async getDataById(identifier, res) {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? User.findById(identifier): User.findOne({ slug: identifier });
        const userData = await query
            .select(`_id name email dial_code phone password password_text role image gender address about city state zipcode terms status updated_by`)
            // .where(`status`).equals(status_active)
            .populate(`role`, `_id name`)
            .populate(`updated_by`, `_id name`)
            .populate(`image`, `_id name path`);

        if (!userData) return res.status(404).json({ message: `User not found!`, data: [] });
        return userData;
    }

    async insertNewData(userData, user_id) {
        const { first_name, middle_name, last_name, email, dial_code, phone, password, role_id } = userData;
        try {
            const hashPassword = await hashPassword(password);

            const user = new User({
                _id: new mongoose.Types.ObjectId(),
                name: {
                    first_name,
                    middle_name,
                    last_name,
                },
                email,
                password: hashPassword,
                password_text: password,
                dial_code: dial_code,
                phone: phoneformate(phone),
                role: role_id,
                updated_by: user_id ?? null,
            });

            const newData = await user.save();
            return newData;
        } catch (err) {
            next(err)
        }
    }

    async update() {

    }

    async delete() {

    }
}
module.exports = new UserService();