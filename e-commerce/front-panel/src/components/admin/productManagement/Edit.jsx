import React, { useEffect, useState } from 'react'
import config from '../../../config'
import { get, patch } from 'utils/AxiosUtils'
import Input from 'components/admin/form/Input'
import Textarea from 'components/admin/form/Textarea'
import SubmitButton from 'components/admin/form/SubmitButton'
import { useDispatch } from 'react-redux'
import { useNavigate, useParams } from 'react-router-dom'
import { useLoading } from 'context/LoadingContext'
import { processNotifications } from 'utils/notificationUtils'
import { checkFileValidation, formattedData } from 'utils/helper'
import { notifyError, notifySuccess, notifyInfo } from 'components/admin/comman/notification/Notification'
import api from 'utils/api'
import SelectDiscount from 'components/admin/form/select/SelectDiscount'
import SelectColor from 'components/admin/form/select/SelectColor'
import SelectTag from 'components/admin/form/select/SelectTag'
import SelectCategory from 'components/admin/form/select/SelectCategory'
import SelectBrand from 'components/admin/form/select/SelectBrand'
import CardForm from 'components/admin/card/CardForm'
import { productValidation, useFormValidation } from 'utils/FormValidation'

function Edit() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const [src, setSrc] = useState(``);
    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);

    const baseUrl = config.reactApiUrl;

    const initialState = {
        name: '',
        description: ``,
        specification: ``,
        price: ``,
        quantity: ``,
        discount: ``,
        brand: ``,
        tags: [],
        categories: [],
        image: ``,
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, productValidation);

    const handleSubmit = async (e) => {
        e.preventDefault();
        notifyInfo(values);
        validateSubmit(e);
        if (errors && Object.keys(errors).length > 0) {
            console.table(errors);
            return false;
        }
        setLoading(true)
        try {
            const newValues = formattedData(values);

            const res = await patch(`/products/${id}`, newValues, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            if (res) {
                resetForm()
                notifySuccess(res.message)
            }
            navigate('/admin/products', { replace: true })
        } catch (err) {
            notifyError(err.message)
        } finally {
            setLoading(false)
        }
    };

    const handleClick = (e) => {
        document.getElementById('imageInput').click();
    };

    const resetForm = () => {
        setValues(initialState);
        setFormKey((prevKey) => prevKey + 1);
        document.getElementsByTagName('form')[0].reset();
    };

    const handleFileUpload = async (e) => {
        const formData = checkFileValidation(e) ? e.target.files[0] : null;
        const imageUrl = URL.createObjectURL(formData);

        const res = await api({
            method: 'post',
            url: `${baseUrl}/products/${id}/image`,
            headers: {
                'Content-Type': 'multipart/form-data',
            }, data: {
                image: formData
            }
        });

        if (res) {
            setSrc(imageUrl);
        }
    };

    useEffect(() => {
        const fetchData = async () => {
            try {

                const [projectData] = await Promise.all([
                    get(`/products/${id}/edit`),
                ]);

                setValues(projectData?.data || {});
                setSrc(projectData?.data?.image?.path);

                processNotifications(200, projectData?.message, dispatch);
            } catch (err) {

                processNotifications(err.status || 500, err.message, dispatch);
            }
        };
        if (id) {
            fetchData();
        }
    }, [dispatch, setValues, id]);


    return (
        <CardForm handleSubmit={handleSubmit} key={formKey}>
            <Input name={`name`} label="Name" value={values?.name} onChange={handleChange} error={errors.name} required={true} inputType={true} />
            <Input name={`price`} text={`price`} label="price" value={values?.price} onChange={handleChange} error={errors.price} required={true} inputType={true} />
            <Input name={`quantity`} text={`quantity`} label="quantity" value={values?.quantity} onChange={handleChange} error={errors.quantity} required={true} inputType={true} />
            <div className={`col-md-4`}>
                <SelectBrand id={`brand`} label={`brand`} value={values.brand_id} handleChange={handleChange} error={errors.brand} required={true} />
            </div>
            <div className={`col-md-4`}>
                <SelectCategory id={`categories`} label={`categories`} value={values.categories} handleChange={handleChange} error={errors.categories} required={true} multiple={true} />
            </div>
            <div className={`col-md-4`}>
                <SelectTag id={`tags`} label={`tags`} value={values.tags} handleChange={handleChange} error={errors.tags} required={true} multiple={true} />
            </div>
            <div className={`col-md-4`}>
                <SelectColor id={`colors`} label={`color`} value={values.colors} handleChange={handleChange} error={errors.colors} required={true} multiple={true} />
            </div>
            <div className={`col-md-4`}>
                <SelectDiscount id={`discount`} label={`discount`} value={values.discount_id} handleChange={handleChange} error={errors.discount} required={true} />
            </div>
            <Textarea name={`description`} className={`w-100`} label="description" value={values?.description} onChange={handleChange} error={errors.description} required={true} inputType={true} ></Textarea>
            <Textarea name={`specification`} className={`w-100`} label="specification" value={values?.specification} onChange={handleChange} error={errors.specification} required={true} inputType={true} ></Textarea>
            <input type={`file`} id={`imageInput`} className={`d-none`} name={`image`} onChange={handleFileUpload} />
            <div className={`col-md-4`}>
                <div className='cursor-none'>
                    <img src={src} alt={`Project main`} className={`rounded-50 col-md-6 image-shell w-50`} onClick={handleClick} loading={`lazy`} />
                </div>
            </div>
            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Updating...' : 'Update Form'} />
            </div>
        </CardForm>
    );
}

export default Edit;
