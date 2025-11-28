import React from 'react';
import Skeleton from 'react-loading-skeleton';

export default function SidebarItemSkeleton({ length = 5 }) {
    return (
        <>
            {Array.from({ length }).map((_, i) => (
                <div key={i}>
                    <li className="nav-item my-1" >
                        <div className="nav-link rounded-pill col-12 collapsed">
                            <Skeleton height={2} width={`80%`} />
                        </div>
                        <ul className="nav-content collapse show">
                            {Array.from({ length: 1 }).map((_, j) => (
                                <li key={j}>
                                    <Skeleton height={6} width={`90%`} />
                                </li>
                            ))}
                        </ul>
                    </li>
                </div>
            ))}
        </>
    );
}
