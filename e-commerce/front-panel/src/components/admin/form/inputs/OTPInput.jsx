// components/admin/form/OTPInput.js
import React, { useRef, useState, useEffect } from 'react';

const OTPInput = ({ length = 6, value = '', onChange , ...props}) => {
  const [otp, setOtp] = useState(Array(length).fill(''));
  const inputsRef = useRef([]);

  useEffect(() => {
    // Load from parent value (for initial OTP)
    if (value) {
      const split = value.split('').slice(0, length);
      setOtp((prev) => split.concat(Array(length - split.length).fill('')));
    }
  }, [value, length]);

  const handleChange = (val, idx) => {
    if (!/^[a-zA-Z0-9]?$/.test(val)) return;
    const newOtp = [...otp];
    newOtp[idx] = val;
    setOtp(newOtp);
    onChange(newOtp.join(''));

    if (val && idx < length - 1) {
      inputsRef.current[idx + 1]?.focus();
    }
  };

  const handleKeyDown = (e, idx) => {
    if (e.key === 'Backspace' && !otp[idx] && idx > 0) {
      inputsRef.current[idx - 1]?.focus();
    }
  };

  const handlePaste = (e) => {
    const paste = e.clipboardData.getData('text').slice(0, length);
    const chars = paste.split('');
    const newOtp = Array(length).fill('');
    chars.forEach((char, i) => newOtp[i] = char);
    setOtp(newOtp);
    onChange(newOtp.join(''));
  };

  return (
    <div style={{ display: 'flex', gap: '8px' }} onPaste={handlePaste} className={`justify-content-center`}>
      {otp.map((digit, idx) => (
        <input
          key={idx}
          type={`text`}
          maxLength={1}
          value={digit}
          ref={(el) => (inputsRef.current[idx] = el)}
          onChange={(e) => handleChange(e.target.value, idx)}
          onKeyDown={(e) => handleKeyDown(e, idx)}
          style={{
            width: '40px',
            height: '40px',
            textAlign: 'center',
            fontSize: '18px',
            border: '1px solid #ccc',
            borderRadius: '4px'
          }}
          className={`form-control`}
          {...props}
        />
      ))}
    </div>
  );
};

export default OTPInput;
