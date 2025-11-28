import { SET_MENU_DATA } from './menuActions';

const initialState = {
    menuData: [],
};

const menuReducer = (state = initialState, action) => {
    switch (action.type) {
        case SET_MENU_DATA:
            return {
                ...state,
                menuData: action.payload,
            };
        default:
            return state;
    }
};

export default menuReducer;