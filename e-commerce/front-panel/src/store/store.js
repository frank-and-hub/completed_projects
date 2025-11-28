import { createStore, applyMiddleware, combineReducers } from 'redux'
import { thunk } from 'redux-thunk'
import dataReducer from './reducer'
import menuReducer from './MenuRedux/menuReducer'
import authSlice from './authSlice'
import permissionSlice from './permissionSlice'
import sideBarSlice from './sideBarSlice'
import roleTableSlice from './roleTableSlice'
import iconSlice from './iconSlice'
import roleSlice from './roleSlice'
import alertSlice from './alertSlice'
import activeMenuSlice from './activeMenuSlice'
import activeSubMenuSlice from './activeSubMenuSlice'
import selectUserSlice from './Select/userSlice'
import selectRoleSlice from './Select/roleSlice'
import selectCategorySlice from './Select/categorySlice'
import selectBannerSlice from './Select/bannerSlice'
import selectBrandSlice from './Select/brandSlice'
import selectColorSlice from './Select/colorSlice'
import selectDiscountSlice from './Select/discountSlice'
import selectSubCategorySlice from './Select/subCategorySlice'
import selectTagSlice from './Select/tagSlice'

const rootReducer = combineReducers({
    menu: menuReducer,
    auth: authSlice,
    data: dataReducer,
    permission: permissionSlice,
    sideBar: sideBarSlice,
    roleTable: roleTableSlice,
    icon: iconSlice,
    role: roleSlice,
    alert: alertSlice,
    active_menu: activeMenuSlice,
    active_sub_menu: activeSubMenuSlice,
    selectUser: selectUserSlice,
    selectRole: selectRoleSlice,
    selectCategory: selectCategorySlice,
    selectBanner: selectBannerSlice,
    selectBrand: selectBrandSlice,
    selectColor: selectColorSlice,
    selectDiscount: selectDiscountSlice,
    selectSubCategory: selectSubCategorySlice,
    selectTag: selectTagSlice,
});

const store = createStore(rootReducer, applyMiddleware(thunk));

export default store;