// notificationUtils.js
import { addAlert } from 'store/alertSlice'

export const processNotifications = (status, message, dispatch) => {
    const notificationData = {
        iconClass: '',
        iconColor: '',
        title: status,
        description: message,
        time: new Date().toLocaleString(),
    };

    if (status >= 200 && status < 300) {
        notificationData.iconClass = 'bi-check-circle';
        notificationData.iconColor = 'text-success';
    } else if (status >= 300 && status < 400) {
        notificationData.iconClass = 'bi-info-circle';
        notificationData.iconColor = 'text-warning';
    } else if (status >= 400 && status < 500) {
        notificationData.iconClass = 'bi-x-circle';
        notificationData.iconColor = 'text-danger';
    } else if (status >= 500) {
        notificationData.iconClass = 'bi-x-circle';
        notificationData.iconColor = 'text-danger';
    }

    dispatch(addAlert(notificationData));
};
