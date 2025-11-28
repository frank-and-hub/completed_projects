import { useState, useEffect } from 'react'
import axios from 'axios'
import { useLoading } from 'components/Admin/Context/LoadingContext';

function useAxios(url) {
  const [data, setData] = useState([]);
  const { loading, setLoading } = useLoading();
  const [error, setError] = useState(null);

  useEffect(() => {
    let isMounted = true; // Track if component is mounted
    setLoading(true)
    axios.get(url)
      .then(response => {
        if (isMounted) {
          setData(response.data);
          setLoading(false)
        }
      })
      .catch(err => {
        if (isMounted) {
          setError(err.message);
        }
      });

    // Cleanup function
    return () => {
      isMounted = false;
    }
  }, [url, setLoading]);

  return { data, loading, error }
}

export default useAxios;
