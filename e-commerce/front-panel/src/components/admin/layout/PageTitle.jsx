import React, { useEffect, useRef } from 'react'
import { Link } from 'react-router-dom'
import { useReRender } from 'context/ReRenderContext'
import { handleToggleSidebar } from 'utils/helper';

function PageTitle({ title, location }) {

    const pathSegments = location.pathname.replace('/admin', '').split('/').filter((x) => x);
    // const [previousUrl, setPreviousUrl] = useState(null);
    const { triggerReRender } = useReRender();
    const prevUrlRef = useRef(location?.pathname);

    useEffect(() => {
        // setPreviousUrl(prevUrlRef.current);
        prevUrlRef.current = location.pathname;
    }, [location]);

    return (
        <>
            <div className={`pagetitle text-left mt-3 pr-2`}>
                <nav className={`d-flex justify-content-between align-items-center`} >
                    <div className={`col-xl-1 col-md-2 col-sm-3 d-none d-sm-block`}>
                        <div className={`d-flex justify-content-around`}>
                            <div className={`shadow btn rounded-pill px-3 mx-2`} onClick={handleToggleSidebar} title={`Toggle`} >
                                <i className={`b bi-list`} ></i> 
                            </div>
                            <div className={`shadow btn rounded-pill px-3 mx-2`} onClick={triggerReRender} title={`Refresh Data`} >
                                <i className={`b bi-arrow-clockwise`} ></i> 
                            </div>
                        </div>
                    </div>
                    <ol className={`breadcrumb m-0 text-capitalize shadow rounded-pill px-3 py-2`}>
                        <li className={`breadcrumb-item`}>
                            <Link to={`/admin/index`} title={`Home Page`}>
                                <i className={`bi bi-house-door`} ></i> 
                                {/* Home */}
                            </Link>
                        </li>
                        {pathSegments.map((segment, index) => {
                            const routeTo = `/admin/${pathSegments.slice(0, index + 1).join('/')}`;
                            const isLast = (index === pathSegments.length - 1);

                            return isLast ? (
                                <li key={index} className={`breadcrumb-item active`} aria-current={`page`}>
                                    {(segment.replace(/-/g, ' '))}
                                </li>
                            ) : (
                                <li key={index} className={`breadcrumb-item`}>
                                    <Link to={routeTo} data-discover={true} >{(segment.replace(/-/g, ' '))}</Link>
                                </li>
                            );
                        })}
                    </ol>

                   
                </nav>
            </div>
        </>
    );
}

export default PageTitle;
