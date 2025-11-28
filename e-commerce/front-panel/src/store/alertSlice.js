import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    notifications: [],
};

const alertSlice = createSlice({
    name: 'alert',
    initialState,
    reducers: {
        setAlerts: (state, action) => {
            state.notifications = action.payload;
        },
        addAlert: (state, action) => {
            state.notifications.push(action.payload);
        },
        removeAlert: (state, action) => {
            state.notifications.splice(action.payload, 1);
        },
        clearAlert: (state, action) => {
            const index = action.payload;
            if (index === undefined) {
                state.notifications = [];
            } else {
                state.notifications.splice(index, 1);
            }
        },
    },
});

export const { setAlerts, addAlert, clearAlert, removeAlert } = alertSlice.actions;

export default alertSlice.reducer;
