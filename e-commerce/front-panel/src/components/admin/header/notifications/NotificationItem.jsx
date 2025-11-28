import React from 'react'

export default function NotificationItem({ iconClass, iconColor, title, description, time, onClick }) {
    return (
        <>
            <li className={`notification-item`}>
                <i className={`bi ${iconClass} ${iconColor}`}></i>
                <div>
                    <h4>{title}</h4>
                    <p>{description}</p>
                    <p>{time}</p>
                </div>
                <button type={`button`} className={`btn-close`} onClick={onClick} data-bs-dismiss={`alert`} aria-label={`Close`}></button>
            </li>
        </>
    )
}
