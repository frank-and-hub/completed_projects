import React, { useContext, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { debounce, formattedData, truncateString, ucwords } from 'utils/helper'
import { notifyError, notifySuccess } from 'components/admin/comman/notification/Notification'
import { destroy, get, patch } from 'utils/AxiosUtils'
import { SidebarContext } from 'context/SidebarContext'
import { useLoading } from 'context/LoadingContext'
import Button from './Button'
import Pageignation from './Pageignation'
import ReusableModal from '../models/ReusableModal'
import TableSkeleton from '../form/skeleton/TableSkeleton'

// Sample data
/**
 * Table component for displaying, searching, sorting, paginating, and managing data in an admin panel.
 *
 * @component
 * @param {Object} props - Component props.
 * @param {string} props.url - The API endpoint to fetch table data from.
 * @param {boolean|function} [props.handelDelete=false] - Enables delete functionality or provides a custom delete handler.
 * @param {function|null} [props.handelFilter=null] - Function to handle filter action.
 * @param {boolean|function} [props.handelCreate=false] - Enables create functionality or provides a custom create handler.
 * @param {boolean|function} [props.handelView=false] - Enables view functionality or provides a custom view handler.
 * @param {boolean|function} [props.handelEdit=false] - Enables edit functionality or provides a custom edit handler.
 * @param {Object} [props.filter] - Additional filter parameters for fetching data.
 * @param {string} props.moduleName - The name of the module (used for routing and display).
 *
 * @returns {JSX.Element} The rendered Table component.
 */

const Table = ({
    url,
    handelDelete = false,
    handelFilter = null,
    handelCreate = false,
    handelView = false,
    handelEdit = false,
    filter,
    moduleName
}) => {

    const { pathname } = useContext(SidebarContext);
    const { loading, setLoading } = useLoading();

    const [data, setData] = useState([]);
    const [dataTableTitle, setDataTableTitle] = useState(null);

    const [searchTerm, setSearchTerm] = useState(``);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    // const [itemsPerPage] = useState(config.pageignation);

    const [dataCount, setDataCount] = useState(0);
    const [dataLimit, setDataLimit] = useState(0);

    const [modalDeleteShow, setModalDeleteShow] = useState(false);
    const [modalStatusShow, setModalStatusShow] = useState(false);

    const [selectedItem, setSelectedItem] = useState(null);
    const [selectedItemName, setSelectedItemName] = useState(null);
    const [selectedItemStatus, setSelectedItemStatus] = useState(null);
    const [sortConfig, setSortConfig] = useState({ key: null, direction: 'desc' });

    const title = `${ucwords((dataTableTitle ?? 'Data') + ' table')}`;
    const cleanedTitle = title.replace(/[?_~/-]/g, ' ');

    /**
     * Handles the search input change event.
     * Debounces the update of the search term to avoid excessive updates,
     * and resets the current page to 1 when the search term changes.
     *
     * @param {React.ChangeEvent<HTMLInputElement>} e - The input change event.
     */
    const handleSearchChange = (e) => {
        debounce(setSearchTerm(e?.target?.value), 5000);
        setCurrentPage(1);
    }

    const handlePageChange = (page) => setCurrentPage(page);

    const tableData = (data && data?.length > 0) ? Object.keys(data[0]) : [];

    const handleSort = (key) => {
        let direction = 'asc';
        if (sortConfig.key === key && sortConfig.direction === 'asc') {
            direction = 'desc';
        }
        setSortConfig({ key, direction });
    }

    const handleDeleteConfirm = async () => {
        if(!loading) setLoading(true)
        try {
            const resDeleted = await destroy(`/${moduleName}/${selectedItem}`);
            notifySuccess(resDeleted.message);
        } catch (err) {
            console.error(err.message);
        } finally {
            setLoading(false)
        }
        setModalDeleteShow(false);
        setSelectedItem(null);
    };

    const handleStatusChangeConfirm = async () => {
        if(!loading) setLoading(true)
        try {
            const newValues = formattedData({ 'status': selectedItemStatus });
            const resUpdated = await patch(`/${moduleName}/${selectedItem}`, newValues);
            notifySuccess(resUpdated.message);
        } catch (err) {
            console.error(err.message);
        } finally {
            setLoading(false)
        }
        setModalStatusShow(false);
        setSelectedItem(null);
    };

    const params = new URLSearchParams({
        ...filter,
        search: (searchTerm.length > 3) ? searchTerm : '',
        page: currentPage,
        orderby: sortConfig?.key ?? '_id',
        direction: sortConfig?.direction
    }).toString();

    useEffect(() => {
        const fetchData = async () => {
            setLoading(true)
            try {
                const res = await get(`${url}?${params}`);

                setData(res?.response?.data)
                setDataLimit(res?.response?.limit)
                setDataCount(res?.response?.count)
                setTotalPages(res?.response?.totalPages)
                setLoading(false)
                setDataTableTitle(res?.title)

            } catch (err) {
                notifyError(err.message || 'An error occurred while fetching data.');
            } finally {
                setLoading(false)
            }
        };

        if (!selectedItem) fetchData();

    }, [setLoading, url, selectedItem, params]);

    const handelDeleteModel = (e) => {
        setSelectedItem(e);
        setModalDeleteShow(true);
    }

    const handelStatusModel = (e) => {
        setSelectedItem(e);
        setModalStatusShow(true);
    }

    const longTextString = (item) => {
        const itemName = (Array.isArray(item)) ? item?.map((obj, idx) => ucwords(obj?.name)).join(', ') : ucwords(item?.name);
        return itemName;
    }

    const start = (currentPage - 1) * dataLimit + 1;
    const end = Math.min(dataCount, currentPage * dataLimit);

    const columnCondication = (header, item, i, data) => {
        let content;
        switch (header) {
            case 'id':
                content = (i++);
                break;
            case 'icon':
                content = <i className={item}></i>;
                break;
            case 'status':
                content = (<Button
                    iconClass={`bi bi-toggle-${(item === true) ? 'on' : 'off'}`}
                    onClick={(e) => {
                        handelStatusModel(data.id);
                        setSelectedItemName(data?.name);
                        setSelectedItemStatus(!data?.status);
                    }}
                    tooltip={`${(item === true) ? 'Active' : 'Inictive'}`}
                    disabled={!item}
                />);
                break;
            case 'image':
                const imageSrc = data?.image?.path;
                content = imageSrc ? (<div className={`w-25 rounded-25`} ><img src={`${imageSrc}`} className={`rounded-circle circle-image-sm`} alt={`#`} loading={`lazy`} /></div>) : (<i className={`bi bi-card-image`}></i>);
                break;
            default:
                if (typeof item === 'object' && item !== null) {
                    content = truncateString(longTextString(item));
                } else {
                    content = truncateString(item);
                }
        }
        return content;
    }

    return (
        <>
            <div className={`card`}>
                <div className={`card-body pb-0`}>
                    <div className={`row my-2`}>
                        <div className={`col-md-5`}>
                            <h6 className={`card-title text-capitalize mb-0`}>{cleanedTitle}</h6>
                        </div>
                        <div className={`d-flex justify-content-evenly align-items-center col-md-7`}>
                            <div className={`col-md-10 col-sm-9 col-8`}>
                                <input
                                    type={`text`}
                                    placeholder={`Search ${moduleName} ...`}
                                    value={searchTerm}
                                    onChange={handleSearchChange}
                                    className={`form-control rounded-pill shadow`}
                                />
                            </div>
                            {handelFilter || handelCreate ? (
                                <div className={`col-md-2 col-sm-3 col-4 d-flex justify-content-evenly`} >
                                    {handelFilter && (
                                        <div className={`col-6 m-auto`}>
                                            <span className={`d-inline-block color`} tabIndex={`0`} data-bs-toggle={`tooltip`} data-bs-original-title={``} title={ucwords(`filter`)}>
                                                <Link onClick={() => handelFilter()} className={`shadow btn btn-sm rounded-circle`}>
                                                    <i className={`bi bi-${true ? `filter-left` : `x-lg`}`}></i>
                                                </Link>
                                            </span>
                                        </div>
                                    )}
                                    {handelCreate && (
                                        <div className={`col-6 m-auto`}>
                                            <span className={`d-inline-block color`} tabIndex={`0`} data-bs-toggle={`tooltip`} title={ucwords(`add`)}>
                                                <Link to={`/admin${pathname}/create`} className={`shadow btn btn-sm rounded-circle`}>
                                                    <i className={`bi bi-plus-lg`}></i>
                                                </Link>
                                            </span>
                                        </div>
                                    )}
                                </div>
                            ) : ``}
                        </div>
                    </div>
                </div>
            </div>
            <div key={`${url}`} className={`card`}>
                <div className={`card-body`}>
                    <div className={`pre-table mt-3`}>
                        {loading ? (
                            <TableSkeleton rows={10} columns={tableData.length} />
                        ) : (
                            <table className={`table table-borderless table-sm datatable table-responsive{-sm|-md|-lg|-xl} mb-2`} style={{ wordWrap: 'normal' }}>
                                <thead>
                                    <tr>
                                        {tableData.length > 0 && (
                                            <>
                                                {tableData.map((header, index) => header !== 'slug' && (
                                                    <th key={index} scope={`row`} className={`text-capitalize cursor${(header !== ('id' || '_id')) ? 'Pointer' : 'Auto'}`} onClick={(e) => (header === ('id' || '_id') ? e.preventDefault() : handleSort(header))}>
                                                        {header === ('id' || '_id') ? (<i className={`bi bi-hash`}></i>) : (ucwords(header))}
                                                        {sortConfig.key === header ? (sortConfig.direction === 'asc' ? '  ▲' : '  ▼') : ''}
                                                    </th>
                                                ))}
                                                <th className={`action`}>Action</th>
                                            </>
                                        )}
                                    </tr>
                                </thead>
                                <tbody>
                                    {(data && data.length > 0 ? (data.map((item, i) => (
                                        <tr key={item.id} className={`p-2`}>
                                            {tableData.map((header, index) => {
                                                const content = columnCondication(header, item[header], (i + ((currentPage - 1) * dataLimit)), item);
                                                return header !== 'slug' && (
                                                    <td key={index}>
                                                        {content}
                                                    </td>);
                                            })}
                                            <td className={`action w-100`}>
                                                <>
                                                    {handelView && (
                                                        <Button
                                                            iconClass={`bi bi-eye`}
                                                            tooltip={ucwords(`view ${item?.name}`)}
                                                            url={`${item?.slug ?? item.id}`} />
                                                    )}
                                                    {item.status === true && handelEdit && (
                                                        <Button
                                                            iconClass={`bi bi-pencil-square`}
                                                            tooltip={ucwords(`edit ${item?.name}`)}
                                                            url={`${item?.slug ?? item.id}/edit`} />
                                                    )}
                                                    {item.status === false && handelDelete && (
                                                        <Button
                                                            iconClass={`bi bi-trash`}
                                                            onClick={() => {
                                                                handelDeleteModel(item.id);
                                                                setSelectedItemName(item?.name);
                                                            }}
                                                            tooltip={ucwords(`delete ${item?.name}`)} />
                                                    )}
                                                </>
                                            </td>
                                        </tr>
                                    ))) : (<tr>
                                        <th colSpan={tableData.length + 1} className={`text-center`}>
                                            <img src={`/admin/img/no-data-found.svg`} alt={`No Data Found!...`} className={`w-25`} />
                                        </th>
                                    </tr>))}
                                </tbody>
                                <tfoot >
                                    {dataCount > dataLimit && (
                                        <tr>
                                            <td colSpan={tableData.length + 1} className={`${tableData.length === 0 ? `p-0` : ``}`} >
                                                <div className={`row justify-content-md-between${tableData?.length > 0 ? ` mt-2` : ``}`}>
                                                    <div className={`col-md-5 col-12 d-none d-md-block`}>
                                                        <div className={`position-absolute my-auto py-2 d-block`}>Showing {start} to {end} of {dataCount} entries</div>
                                                    </div>
                                                    <div className={`col-md-7 col-12`}>
                                                        <div className={`position-relative Page navigation`}>
                                                            <Pageignation totalPages={totalPages} currentPage={currentPage} handlePageChange={handlePageChange} />
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    )}
                                </tfoot>
                            </table>
                        )}
                    </div>
                </div>
            </div>
            <ReusableModal
                show={modalDeleteShow}
                handleClose={() => setModalDeleteShow(false)}
                title={`Confirm Delete`}
                body={`Are your want to delete ${selectedItemName ?? 'item'}`}
                primaryAction={handleDeleteConfirm}
                primaryLabel={`Delete`}
                primaryVariant={`danger`}
                secondaryLabel={`Cancel`}
            />
            <ReusableModal
                show={modalStatusShow}
                handleClose={() => setModalStatusShow(false)}
                title={`Status Change`}
                body={`Are your want to ${selectedItemName ?? ''} status`}
                primaryAction={handleStatusChangeConfirm}
                primaryLabel={`Change`}
                secondaryLabel={`Cancel`}
            />
        </>
    );
}

export default Table