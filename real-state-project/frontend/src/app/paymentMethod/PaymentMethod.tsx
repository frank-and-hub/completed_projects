import { ActionIcon, Loader } from "@mantine/core";
import { IconChevronLeft } from "@tabler/icons-react";
import ModalCloseIcon from "../home/components/modalCloseIcon/ModalCloseIcon";
import "./paymentMethod.scss";
import usePayment from "./usePayment";
import { useState } from "react";
import CustomButton from "@/components/customButton/CustomButton";
function PaymentMethod({ handleClose }: any) {
  const { isPending, data, amount, amountLoading, mutate, subscriptionId } =
    usePayment(handleClose);

  const [isLoading, setIsLoading] = useState<boolean>(false);
  return (
    <div className="paymentmethod_modal_card" style={{ minHeight: 300 }}>
      {isPending ? (
        <Loader
          style={{
            position: "absolute",
            top: "50%",
            left: "50%",
            transform: "translate(-50%, -50%)",
          }}
        />
      ) : (
        <>
          <div className="modal_head_close cenetr_back">
            {/* <ActionIcon className="back_btn">
              <IconChevronLeft stroke={2} />
            </ActionIcon> */}
            <h2>Continue</h2>
            <ModalCloseIcon handleClose={handleClose} />
          </div>

          <div className="pay_edits_bg">
            <h4>Selected Plans</h4>
            <div className="plans_range">
              <h1>
                {amount}
                <span>Rand</span>
              </h1>
              <h6>Per month</h6>
              {/* <Anchor href="javascript:;">Edit Plan</Anchor> */}
            </div>
          </div>
          {parseFloat(amount) === 0 ? (
            <CustomButton
              loading={amountLoading}
              onClick={() => {
                mutate({ subscription_id: subscriptionId });
              }}
            >
              Proceed
            </CustomButton>
          ) : (
            <form
              // action="https://www.payfast.co.za/eng/process"
              action="https://sandbox.payfast.co.za/eng/process"
              onSubmit={() => {
                setIsLoading(true);
              }}
            >
              <input
                type="hidden"
                id="fname"
                name="merchant_id"
                value={data?.data?.merchant_id}
                style={{ height: 0 }}
              />
              <input
                type="hidden"
                id="lname"
                name="merchant_key"
                value={data?.data?.merchant_key}
                style={{ height: 0 }}
              />
              <input
                type="hidden"
                id="lname"
                name="return_url"
                value={data?.data?.return_url}
                style={{ height: 0 }}
              />
              <input
                type="hidden"
                id="lname"
                name="cancel_url"
                value={data?.data?.cancel_url}
                style={{ height: 0 }}
              />
              <input
                type="hidden"
                id="lname"
                name="notify_url"
                value={data?.data?.notify_url}
                style={{ height: 0 }}
              />
              <input
                type="hidden"
                id="lname"
                name="name_first"
                value={data?.data?.name_first}
                style={{ height: 0 }}
              />
              <input
                type="hidden"
                id="lname"
                name="name_last"
                value={data?.data?.name_last}
                style={{ height: 0 }}
              />
              <input
                type="hidden"
                id="lname"
                name="email_address"
                value={data?.data?.email_address}
                style={{ height: 0 }}
              />
              <input
                type="hidden"
                id="lname"
                name="m_payment_id"
                value={data?.data?.m_payment_id}
                style={{ height: 0 }}
              />
              <input
                type="hidden"
                id="lname"
                name="amount"
                value={data?.data?.amount}
                style={{ height: 0 }}
              />
              <input
                type="hidden"
                id="lname"
                name="item_name"
                value={data?.data?.item_name}
                style={{ height: 0 }}
              />
              <input
                type="hidden"
                id="lname"
                name="signature"
                value={data?.data?.signature}
                style={{ height: 0 }}
              />
              <input
                disabled={isLoading ? true : false}
                type="submit"
                value="Proceed"
                style={{
                  backgroundColor: isLoading ? "#808080" : "#F30051",
                  marginTop: 10,
                  padding: "10px 40px",
                  borderRadius: 20,
                  color: "white",
                  cursor: "pointer",
                }}
              />
            </form>
          )}
        </>
      )}
    </div>
  );
}

export default PaymentMethod;
{
  /* <Box className='card_detaile'>
                <ul>
                    <li>
                        <h6>Card details </h6>
                        <p>Enter the 16 digit card number on the card</p>
                        <Group className='card_nb_flex'>
                            <Input placeholder="1245" />
                            <Input placeholder="8672" />
                            <Input placeholder="4265" />
                            <Input placeholder="3456" />
                            <span>
                              <Image src={CardImg} alt="" />
                            </span>
                        </Group>
                    </li>
                    <li className="row_inputs_crd">
                        <figcaption>
                            <h6>CVV Number </h6>
                            <p>Enter the 3 digit card number on the card </p>
                        </figcaption>
                        <div className='cvv_pin'>
                            <Input placeholder="1245" />
                        </div>
                    </li>
                    <li className="row_inputs_crd">
                        <figcaption>
                            <h6>Expiry Date</h6>
                            <p>Enter the expiry date written on back of  the card </p>
                        </figcaption>
                        <div className='expiry_input'>
                            <Input placeholder="09" />
                              <span>/</span>
                            <Input placeholder="27" />
                        </div>
                    </li>
                </ul>

                <CustomButton>Proceed</CustomButton>
            </Box> */
}
