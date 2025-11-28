import React, { useEffect, useState } from 'react'
import config from '../../../config'
import api from 'utils/api'
import { Link } from 'react-router-dom'
import { notifyError, notifySuccess } from 'components/admin/comman/notification/Notification.js'
import { useAuth } from 'utils/AuthContext'
import { checkFileValidation, getFullName } from 'utils/helper.js'

function ProfileCard({ user }) {
    const [src, setSrc] = useState('/img/profile-img.jpg');
    const { loadUser } = useAuth();

    const baseUrl = config.reactApiUrl;

    const userId = user?._id ?? localStorage.getItem('user_id');

    const handleClick = (e) => {
        document.getElementById('imageInput').click();
    };

    const handleFileUpload = async (e) => {
        const formData = checkFileValidation(e) ? e.target.files[0] : null;
        try {
            // Post the form data to the server
            const response = await api({
                method: 'post',
                url: `${baseUrl}/users/${userId}/image`,
                headers: {
                    'Content-Type': 'multipart/form-data',
                }, data: {
                    image: formData
                }
            });

            notifySuccess(response.message);
            setSrc(response.imagePath);
            loadUser();
        } catch (error) {
            notifyError(`Error uploading the image: ${error}`);
        }
    };

    useEffect(() => {
        setSrc(user?.image?.path ?? `/admin/img/profile-img.jpg`);
    }, [user, src]);

    return (
        <>
            <div key={Math.floor(Math.random() * 1000)} className={`card pb-3`}>
                <div className={`card-body profile-card pt-4 d-flex flex-column align-items-center`}>
                    <form encType={`multipart/form-data`} >
                        <img src={src} alt={`Profile`} className={`rounded-circle circle-image`} onClick={handleClick} style={{ cursor: 'pointer' }} loading={`lazy`} />
                        <input type={`file`} id={`imageInput`} className={`d-none`} name={`image`} onChange={handleFileUpload} />
                    </form>
                    <h2>{getFullName(user?.name)}</h2>
                    <h3 key={0}>{user?.email}</h3>
                    <div className={`social-links mt-2 w-100`}>
                        {user?.social_details && user?.social_details.length > 0 && user?.social_details.map((social) => (
                            <Link to={`${social?.url}`} className={social?.name} ><i className={`bi bi-${social?.icon}`}></i></Link>
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}

export default ProfileCard;
