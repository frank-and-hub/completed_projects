import React, { useEffect, useState } from 'react'

export const Loading = () => {
    const [count, setCount] = useState(0);

    const speed = 100;

    useEffect(() => {
        const interval = setInterval(() => {
            setCount(prevCount => {
                if (prevCount < 99) {
                    return prevCount + 1;
                } else {
                    clearInterval(interval);
                    return prevCount;
                }
            });
        }, speed);

        return () => clearInterval(interval);
    }, []);
    return (
        <>
            <div className={`object-fit-contain w-100 h-100`} >
                <div className="loading position-absolute rounded-pill">
                    {`${count} %`}
                    {/* <img src={`/admin/img/loader.gif`} alt={`loading...`} className={`loading position-absolute`} loading={`lazy`}/> */}
                </div>
            </div>
        </>
    )
}
