import React, { useEffect, useState } from 'react'
import { get } from 'utils/AxiosUtils'
import Input from 'components/admin/form/Input'
import { useParams } from 'react-router-dom'
import { processNotifications } from 'utils/notificationUtils'
import { useDispatch } from 'react-redux'
import SelectBrand from 'components/admin/form/select/SelectBrand'
import SelectCategory from 'components/admin/form/select/SelectCategory'
import SelectTag from 'components/admin/form/select/SelectTag'
import SelectColor from 'components/admin/form/select/SelectColor'
import SelectDiscount from 'components/admin/form/select/SelectDiscount'
import Textarea from 'components/admin/form/Textarea'
import CardForm from 'components/admin/card/CardForm'

function View() {
    const [src, setSrc] = useState(``);
    const { id } = useParams();
    const dispatch = useDispatch();
    const [values, setValues] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [projectData] = await Promise.all([
                    get(`/products/${id}`),
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
    }, [dispatch, id]);

    const handleChange = (e) => {
        e.preventDefault()
    }

    return (
        <CardForm handleSubmit={(e) => e.preventDefault()} key={0}>
            <Input name={`name`} label="Name" value={values?.name} onChange={handleChange} required={false} disabled={true} inputType={true} />
            <Input name={`price`} text={`price`} label="price" value={values?.price} onChange={handleChange} required={false} disabled={true} inputType={true} />
            <Input name={`quantity`} text={`quantity`} label="quantity" value={values?.quantity} onChange={handleChange} required={false} disabled={true} inputType={true} />
            <div className={`col-md-4`}>
                <SelectBrand id={`brand_id`} label={`brand`} value={values?.brand_id} handleChange={handleChange} required={false} disabled={true} />
            </div>
            <div className={`col-md-4`}>
                <SelectCategory id={`categories`} label={`categories`} value={values?.categories} handleChange={handleChange} required={false} disabled={true} multiple={true} />
            </div>
            <div className={`col-md-4`}>
                <SelectTag id={`tags`} label={`tags`} value={values?.tags} handleChange={handleChange} required={false} disabled={true} multiple={true} />
            </div>
            <div className={`col-md-4`}>
                <SelectColor id={`colors`} label={`colors`} value={values?.colors} handleChange={handleChange} required={false} disabled={true} multiple={true} />
            </div>
            <div className={`col-md-4`}>
                <SelectDiscount id={`discount_id`} label={`discount`} value={values?.discount_id} handleChange={handleChange} required={false} disabled={true} />
            </div>
            <Textarea name={`description`} className={`w-100`} label="description" value={values?.description} onChange={handleChange} required={false} disabled={true} inputType={true} ></Textarea>
            <Textarea name={`specification`} className={`w-100`} label="specification" value={values?.specification} onChange={handleChange} required={false} disabled={true} inputType={true} ></Textarea>
            <input type={`file`} id={`imageInput`} className={`d-none`} name={`image`} onChange={(e) => e.preventDefault()} />
            <div className={`col-md-4`}>
                <div className='cursor-none'>
                    <img src={src} alt={`Project main`} className={`rounded-50 col-md-6 image-shell w-50`} onClick={(e) => e.preventDefault()} loading={`lazy`} />
                </div>
            </div>
            <div className={`col-12`}>
            </div>
        </CardForm>
    );
}

export default View;
