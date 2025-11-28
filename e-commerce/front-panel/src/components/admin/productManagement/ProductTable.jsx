import React, { useState } from 'react'
import Table from 'components/admin/table/Table'
import { useLoading } from 'context/LoadingContext'
import SelectForm from 'components/admin/form/select/SelectForm'
import SubmitButton from 'components/admin/form/SubmitButton'
import { StatusOptions } from 'utils/selects'
import SelectBrand from 'components/admin/form/select/SelectBrand'
import SelectCategory from 'components/admin/form/select/SelectCategory'
import SelectColor from 'components/admin/form/select/SelectColor'
import SelectDiscount from 'components/admin/form/select/SelectDiscount'
import SelectTag from 'components/admin/form/select/SelectTag'
import { useFormValidation, validate } from 'utils/FormValidation'

function ProductTable() {
    const module = 'products';
    const [filter, setFilter] = useState({});
    const [formKey, setFormKey] = useState(0);
    const { loading, setLoading } = useLoading();
    const [showTable, setShowTable] = useState(true);
    const [showFilter, setShowFilter] = useState(false);

    const handelFilter = (e) => {
        setShowFilter(!showFilter);
    }

    const initialState = {
        user_id: '',
        category_id: ``,
        sub_category_id: ``,
        brand_id: ``,
        color_id: ``,
        discount_id: ``,
        tag_id: ``,
        status: ''
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, validate);

    const handleSubmit = async (e) => {
        setShowTable(false);
        e.preventDefault();
        setLoading(true)
        validateSubmit(e);
        if (errors && Object.keys(errors).length > 0) {
            console.table(errors);
            return false;
        }
        try {
            setFilter({ ...values });
            setShowTable(true);
        } catch (err) {
            console.error(err.message);
        } finally {
            setLoading(false)
        }
    };

    const resetForm = () => {
        setValues(initialState);
        setShowTable(false);
        setFormKey((prevKey) => prevKey + 1);
        document.getElementsByTagName('form')[0].reset();
    };

    const handlers = {
        url: `/${module}`,
        handelView: true,
        handelEdit: true,
        handelDelete: true,
        handelFilter,
        handelCreate: true,
        moduleName: module,
        filter
    };

    return (
        <>
            {showFilter && (<div className={`card`}>
                <div className='card-title pb-0 mb-0 text-capitalize tshadow'>
                    {(module)} Filter
                </div>
                <div className={`card-body`}>
                    <form key={formKey} encType={`multipart/form-data`} className={`row m-0 g-4 needs-validation`} onSubmit={handleSubmit} noValidate>
                        <div className={`col-md-4`}>
                            <SelectBrand id={`brand_id`} label={`brand`} value={values.brand_id} handleChange={handleChange} error={errors.brand_id} required={false} />
                        </div>
                        <div className={`col-md-4`}>
                            <SelectCategory id={`category_id`} label={`category`} value={values.category_id} handleChange={handleChange} error={errors.category_id} required={false} mulitiple={false} />
                        </div>
                        <div className={`col-md-4`}>
                            <SelectColor id={`color_id`} label={`color`} value={values.color_id} handleChange={handleChange} error={errors.color_id} required={false} mulitiple={false} />
                        </div>
                        <div className={`col-md-4`}>
                            <SelectDiscount id={`discount_id`} label={`discount`} value={values.discount_id} handleChange={handleChange} error={errors.discount_id} required={false} />
                        </div>
                        <div className={`col-md-4`}>
                            <SelectTag id={`tag_id`} label={`tag`} value={values.tag_id} handleChange={handleChange} error={errors.tag_id} required={false} mulitiple={false} />
                        </div>
                        <div className={`col-md-4`}>
                            <SelectForm id="status" label={`Status`} value={values.status} handleChange={handleChange} error={errors.status} required={false} Options={StatusOptions} />
                        </div>
                        <div className={`col-md-12`}>
                            <SubmitButton className={`custom`} name={loading ? 'Loading...' : 'apply filter'} disabled={!showTable} />
                            <SubmitButton className={`secondary`} name={`Reset`} type='button' onClick={resetForm} />
                        </div>
                    </form>
                </div>
            </div>)}
            {showTable && (
                <Table table {...handlers} />
            )}
        </>
    )
}

export default ProductTable