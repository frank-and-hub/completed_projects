import { useEffect, useState, useTransition } from "react"
import { get, post, put, patch, destroy } from '../utils/AxiosUtils'
import { useSelector, useDispatch } from 'react-redux'
import { processNotifications } from "utils/notificationUtils"
import { useAuth } from 'utils/AuthContext'

const useApi = (apiCall, url, bodyData = null) => {
    const { logout } = useAuth();
    const dispatch = useDispatch();
    const token = useSelector((state) => (state.auth.token));
    const [response, setResponse] = useState(null);
    const { loading, setLoading } = useState(false);
    const [error, setError] = useState(null);
    const [isPending, startTransition] = useTransition();

    const redirectToLogin = ((err) => {
        if (err.status === 401) logout();
        console.error(err.message);
    }, []);

    useEffect(() => {
        const fetchData = async () => {
            startTransition(async () => {
                setLoading(true)
                setError(null);
                try {
                    console.time(apiCall);
                    const res = bodyData ? await apiCall(url, bodyData, token) : await apiCall(url, token);
                    setResponse(res);
                    console.timeEnd(apiCall);
                    processNotifications(200, res?.message, dispatch);
                } catch (err) {
                    if (err?.response && err?.response?.status === 401) redirectToLogin(err);
                    // setError('An error occurred while fetching data.', err.response);
                    processNotifications(err.status, err.message, dispatch);
                } finally {
                    setLoading(false)
                    // console.trace();
                }
            });
        }
        fetchData();
    }, [url, bodyData, apiCall, setResponse, setError, setLoading, token, dispatch, redirectToLogin]);

    if (isPending) {
        console.info(`loading...`);
    }

    return { response, loading, error }
}

export const GetData = (url) => useApi(get, url);
export const PostData = (url, data) => useApi(post, url, data);
export const PutData = (url, data) => useApi(put, url, data);
export const PatchData = (url, data) => useApi(patch, url, data);
export const DeleteData = (url) => useApi(destroy, url);