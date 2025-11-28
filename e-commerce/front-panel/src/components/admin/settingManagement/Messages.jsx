import React, { useEffect, useState } from 'react'
import { get } from 'utils/AxiosUtils'
import { useSelector } from 'react-redux';

export const Messages = ({ isDataChange }) => {

    const [prevoiusChat, setPrevoiusChat] = useState([]);
    const user = useSelector((state) => (state.auth.user));

    useEffect(() => {
        const fetchPrevoiusChat = async (receiverId) => {

            try {
                const [resPreviousChatData] = await Promise.all([
                    get(`/messages/${receiverId}`),
                ]);

                setPrevoiusChat(resPreviousChatData?.data || []);
            } catch (err) {
                console.error(err.message);
                setPrevoiusChat([]);
            }
        };

        if (isDataChange) {
            fetchPrevoiusChat(isDataChange);
        } else {
            setPrevoiusChat([]);
        }
    }, [isDataChange]);

    return (
        <div className={`col-md-12 chat-container border-0 row w-100`}>
            {prevoiusChat && prevoiusChat.length > 0 ? (
                prevoiusChat.map((val, index) => (
                    <div
                        key={index}
                        className={`chat-message col-7 ${val?.sender?._id === user?.id ? 'sender' : 'receiver'}`}
                    >
                        <span>{val.message}</span>
                    </div>
                ))
            ) : (
                <div className={`no-chat-message`}>No previous chat found.</div>
            )}
        </div>
    );
}