'use client';

import React, { useEffect, useState } from 'react';
import { get, destroy, patch } from '@/utils/axios';
import { ActionIcon, Box, LoadingOverlay, Switch, Table } from '@mantine/core';
import { useDebounce } from '@/hooks/useDebounce';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';
import { c, ce } from '@/utils/console';
import InputText from '@/components/inputs/InputText';
import SortTablePagination from '@/components/table/SortTablePagination';
import { sanitizeFormData, sleep, truncate } from '@/utils/helpers';
import ReusableModal from '@/components/table/TableModal';
import TableDataShort from '@/components/table/TableDataShort';
import TableButton from '@/components/table/TableButton';
import { useIsMobile } from '@/components/hooks/useIsMobile';
import { useFormContext } from '@/context/FormContext';
import { CommonQueryParams, PaginatedResponse, TableViewProps } from '@/types/CommonQueryParams';
import { AdjustmentsHorizontalIcon, EyeIcon, PencilSquareIcon, TrashIcon } from '@heroicons/react/24/outline';

export const TableView: React.FC<TableViewProps> = ({
    resource,
    columns,
    addUrl,
    editUrl,
    viewUrl,
    canDelete,
    filters,
}) => {
    const { loading, setLoading, setPageTitle } = useAuth();
    const [data, setData] = useState<any[]>([]);
    const [limit, setLimit] = useState<number>(10);
    const [page, setPage] = useState<number>(1);
    const [total, setTotal] = useState<number>(0);
    const [orderBy, setOrderBy] = useState<string>('createdAt');
    const [direction, setDirection] = useState<'asc' | 'desc'>('desc');
    const [search, setSearch] = useState('');
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showStatusModal, setShowStatusModal] = useState(false);
    const [selectedItem, setSelectedItem] = useState<any>(null);
    const { resetForm } = useFormContext();
    const [filterValues, setFilters, showFilters, setShowFilters] = filters;

    const router = useRouter();
    const debouncedSearch = useDebounce(search, 500);
    const totalPages = Math.ceil(total / limit);

    const fetchData = async () => {
        if (page == 1) setLoading(true);
        const rawParams: CommonQueryParams = {
            page,
            limit,
            orderBy,
            direction,
            search: debouncedSearch,
            ...(filterValues || {}),
        };

        const filteredParams = sanitizeFormData(rawParams);

        try {
            const query = new URLSearchParams(filteredParams as any).toString();
            const res: PaginatedResponse<any> = await get(`v1/${resource}?${query}`);

            if (res) {
                setData(res?.data);
                // if (res.data?.length > 0) {
                setPageTitle(res?.title || '');
                setTotal(res?.pagination?.total ?? 0);
                setLoading(false);
            }
        } catch (err) {
            ce('Failed to fetch data:', err);
        }
    };

    useEffect(() => {
        fetchData();
    }, [page, orderBy, direction, limit, resource, debouncedSearch, filterValues]);

    const onDelete = async (id: number | string) => {
        // if (!confirm('Are you sure you want to delete this item?')) return;
        try {
            await destroy(`/${resource}/${id}`);
            setShowDeleteModal(false);
            fetchData();
        } catch (err) {
            ce('Delete failed:', err);
        }
    };

    const onStatusChange = async (id: number | string) => {
        // if (!confirm('Change status for this item?')) return;
        try {
            const res = await patch(`/${resource}/${id}`, { 'status': selectedItem?.status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE' }); // Replace with PATCH if needed
            console.log(`object res:`, res.message?.statusCOde);
            setShowStatusModal(false);
            fetchData();
        } catch (err) {
            ce('Status update failed:', err);
        }
    };

    const handleSort = (key: string) => {
        if (orderBy === key) {
            setDirection(prev => (prev === 'asc' ? 'desc' : 'asc'));
        } else {
            setOrderBy(key);
            setDirection('asc');
        }
        setPage(1);
    };

    const handleClose = () => {
        setShowDeleteModal(false);
        setShowStatusModal(false);
        setSelectedItem(null);
    };

    const filterText = useIsMobile(720) ? <AdjustmentsHorizontalIcon title={`Filters`} className={`w-5`} /> : showFilters ? 'Hide Filters' : 'Show Filters';

    return (
        <>
            <div className={`table-container`}>
                <div className={`flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 py-5`}>
                    <div className={`max-w-full min-w-1/4`}>
                        <InputText
                            label={showFilters ? `Search` : ``}
                            type={`text`}
                            name={`search`}
                            placeholder={`Search...`}
                            value={search}
                            onChange={(e: any) => {
                                setSearch(e.target.value);
                                setPage(1);
                            }}
                        // className={`w-full md:w-full lg:w-72`}
                        />
                    </div>
                    <div className={`flex sm:flex-row sm:items-center sm:gap-3 sm:w-auto max-w-full min-w-1/4 gap-4 justify-end sm:justify-end`}>
                        <div className={`bock md:hidden`}>
                            <TableDataShort limit={limit} setLimit={setLimit} showLabel={false} />
                        </div>
                        {addUrl && addUrl.length > 0 && (
                            <div>
                                <TableButton
                                    onClick={() => router.push(addUrl)}
                                    name={`Add New`}
                                />
                            </div>
                        )}
                        {filters && filterValues && (
                            <div>
                                <TableButton
                                    onClick={() => { setShowFilters(!showFilters), setFilters({}), resetForm() }}
                                    name={filterText}
                                />
                            </div>
                        )}
                    </div>
                </div>
                <Box pos={`relative`}>
                    <LoadingOverlay visible={loading} loaderProps={{ children: 'Loading...' }} />
                    {!loading && !data?.length ? (
                        <p className={`text-center`}>No data found.</p>
                    ) : (
                        <>
                            <Table.ScrollContainer minWidth={800} type={`native`} className={`overflow-y-auto`} maxHeight={400} style={{ scrollbarWidth: 'none' }}>
                                <Table className={`w-full text-sm`} verticalSpacing={`xs`} >
                                    <Table.Thead>
                                        <Table.Tr>
                                            {columns.map((col, i) => {
                                                const isActive = orderBy === col.key;
                                                const isAsc = direction === 'asc';
                                                return (
                                                    <Table.Th key={col.key || i} className={`text-left px-2 py-1 cursor-pointer select-none`} onClick={() => handleSort(col.key)}>
                                                        <span className={`flex items-center gap-1`}>{col.label} {isActive && (<span className="text-xs">{isAsc ? '▲' : '▼'}</span>)}</span>
                                                    </Table.Th>
                                                );
                                            })}
                                            <Table.Th className={`px-2 py-1`}>Actions</Table.Th>
                                        </Table.Tr>
                                    </Table.Thead>

                                    <Table.Tbody>
                                        {data?.map((row, i) => (
                                            <Table.Tr key={row.id || i} className={`border-t`}>
                                                {columns.map((col) => {
                                                    let td;
                                                    switch (col.key) {
                                                        case 'id':
                                                            td = <Table.Td className={`px-2 py-1`} key={col.label} >{(page - 1) * limit + i + 1}</Table.Td>
                                                            break;
                                                        case 'status':
                                                            td = <Table.Td className={`align-middle`} key={col.key}><Switch checked={row.status === 'ACTIVE' ? true : false} onChange={() => { setShowStatusModal(true), setSelectedItem(row), c(`This is a status item :`, row) }} color={`dark`} size={`xs`} className={`text-shadow-2xs flex justify-start text-white transition-all duration-200 rounded-xl disabled:bg-red-400 disabled:text-white `} withThumbIndicator={true} title={`Status`} /></Table.Td>;
                                                            break;
                                                        default:
                                                            td = <Table.Td key={col.key} className={`px-2 py-1`}>{truncate(row[col.key], 25)}</Table.Td>;
                                                            break;
                                                    }
                                                    return td;
                                                })}
                                                <Table.Td className={`px-2 py-1`}>
                                                    <div className={`flex items-center gap-2 shadow-2xl bg-transparent justify-start`}>
                                                        {viewUrl && viewUrl.length > 0 && (
                                                            <ActionIcon
                                                                variant={`transparent`}
                                                                size={`xs`}
                                                                className={`shadow-2xl p-.5`}
                                                                aria-label="View"
                                                                color={`dark`}
                                                                title={`View`}
                                                                onClick={() => router.push(viewUrl(row.id))}
                                                            >
                                                                <EyeIcon />
                                                            </ActionIcon>
                                                        )}
                                                        {editUrl && editUrl.length > 0 && (
                                                            <ActionIcon
                                                                variant={`transparent`}
                                                                size={`xs`}
                                                                className={`shadow-2xl p-.5`}
                                                                aria-label="Edit"
                                                                color={`dark`}
                                                                title={`Edit`}
                                                                onClick={() => router.push(editUrl(row.id))}
                                                            >
                                                                <PencilSquareIcon />
                                                            </ActionIcon>
                                                        )}
                                                        {canDelete && (
                                                            <ActionIcon
                                                                variant={`transparent`}
                                                                size={`xs`}
                                                                className={`shadow-2xl p-.5`}
                                                                aria-label="Delete"
                                                                color={`dark`}
                                                                title={`Delete`}
                                                                onClick={() => { setShowDeleteModal(true), setSelectedItem(row), c(`This is a deleted item :`, row) }}
                                                            >
                                                                <TrashIcon />
                                                            </ActionIcon>
                                                        )}
                                                    </div>
                                                </Table.Td>
                                            </Table.Tr>
                                        ))}
                                    </Table.Tbody>
                                    <Table.Tfoot></Table.Tfoot>
                                </Table>
                            </Table.ScrollContainer>
                            <SortTablePagination total={total} totalPages={totalPages} page={page} limit={limit} setPage={setPage} setLimit={setLimit} />
                        </>
                    )}
                </Box>
            </div>
            <ReusableModal show={showDeleteModal} handleClose={handleClose} title={`Delete Item`} body={`Are you sure you want to delete ?`} primaryLabel={`Delete`} secondaryLabel={`Cancel`} primaryAction={() => onDelete(selectedItem?.id)} primaryVariant={`danger`} secondaryVariant={`danger`} />
            <ReusableModal show={showStatusModal} handleClose={handleClose} title={`Update Status`} body={`Are you sure you want to ${selectedItem?.status == 'active' ? 'inactive' : 'active'} item status ?`} primaryLabel={`Update`} secondaryLabel={`Cancel`} primaryAction={() => onStatusChange(selectedItem?.id)} primaryVariant={`filled`} secondaryVariant={`danger`} />
        </>
    );
};
