import React from 'react'
import Skeleton from 'react-loading-skeleton';
import 'react-loading-skeleton/dist/skeleton.css';


export default function TableSkeleton({ rows = 5, columns = 4 }) {
    return (
        <table className={`table table-borderless datatable mb-2`} style={{ wordWrap: 'normal' }}>
            <thead>
                <tr>
                    {Array.from({ length: columns }).map((_, i) => (
                        <th key={i}><Skeleton height={20} /></th>
                    ))}
                </tr>
            </thead>
            <tbody>
                {Array.from({ length: rows }).map((_, rowIdx) => (
                    <tr key={rowIdx}>
                        {Array.from({ length: columns }).map((_, colIdx) => (
                            <td key={colIdx}><Skeleton height={20} /></td>
                        ))}
                    </tr>
                ))}
            </tbody>
        </table>
    );
}
