import { radius } from '@/utils/style';
import { NativeSelect } from '@mantine/core';
import React from 'react';

interface TableDataShortProps {
    limit: number;
    setLimit: (limit: number) => void;
    showLabel: boolean;
    [x: string]: any;
}

function TableDataShort({ limit, setLimit, showLabel, ...rest }: TableDataShortProps) {
    return (
        <div className={`w-24`}>
            <NativeSelect
                label={showLabel ? `Shot By` : ``}
                data={[
                    { label: '10', value: '10' },
                    { label: '20', value: '20' },
                    { label: '50', value: '50' },
                    { label: '100', value: '100' },
                ]}
                value={limit.toString()}
                onChange={(e) => setLimit(Number(e.currentTarget.value))}
                size={`sm`}
                radius={radius}
                classNames={{
                    input: 'text-gray-600 dark:text-gray-100 border border-gray-200 dark:bg-gray-100 dark:border-gray-700 rounded-2xl bg-transparent shadow-xl transition-all duration-500 ease-in-out hover:shadow-lg hover:scale-[0.99] hover:text-black',
                    label: 'text-xs text-gray-500 mb-0 dark:text-gray-100',
                }}
                {...rest}
            />
        </div>
    );
}

export default TableDataShort;
