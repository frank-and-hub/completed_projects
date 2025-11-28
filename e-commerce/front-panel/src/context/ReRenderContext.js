import React, { createContext, useState, useContext } from 'react';

const ReRenderContext = createContext();

export const ReRenderProvider = ({ children }) => {
  const [renderKey, setRenderKey] = useState(0);

  const triggerReRender = () => {
    setRenderKey((prevKey) => prevKey + 1);
  };

  return (
    <ReRenderContext.Provider value={{ renderKey, triggerReRender }}>
      {children}
    </ReRenderContext.Provider>
  );
};

export const useReRender = () => useContext(ReRenderContext);
