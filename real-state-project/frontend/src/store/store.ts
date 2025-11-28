import storage from 'redux-persist/lib/storage';
import { configureStore } from '@reduxjs/toolkit';
import { persistReducer, persistStore } from 'redux-persist';
import rootReducer from './reducer/rootReducer';
const persistConfig = {
  key: 'PocketProperty',
  storage,
};

const root_reducer = (state: any, action: any) => {
  let reduxState = state;
  if (action.type === 'LOGOUT') {
    if (state) {
      for (let [key, value] of Object.entries(reduxState)) {
        if (key === 'baseUrlReducer') {
          reduxState[key] = value;
        } else {
          reduxState[key] = undefined;
        }
      }
      state = reduxState;
    }
  }

  return rootReducer(state, action);
};

export const makeStore = () => {
  const persistedReducer = persistReducer(persistConfig, root_reducer);
  return configureStore({
    reducer: persistedReducer,
  });
};
export const store = makeStore();
export const persistor = persistStore(store);
// Infer the type of makeStore
// export type AppStore = ReturnType<typeof makeStore>;
// // Infer the `RootState` and `AppDispatch` types from the store itself
// // export type RootState = ReturnType<AppStore['getState']>;
// export type RootState = ReturnType<typeof store.getState>;

// export type AppDispatch = typeof store.dispatch;
export type AppStore = ReturnType<typeof makeStore>;
// Infer the `RootState` and `AppDispatch` types from the store itself
export type RootState = ReturnType<AppStore['getState']>;
export type AppDispatch = AppStore['dispatch'];
