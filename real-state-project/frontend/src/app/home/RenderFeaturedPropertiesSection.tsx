'use client'
import { useAppSelector } from '@/store/hooks'
import React from 'react'
import FeaturedPropertiesSection from './FeaturedPropertiesSection'

function RenderFeaturedPropertiesSection() {
    const {token} = useAppSelector(state=>state?.userReducer)
  return (
    <div> {token?null:<FeaturedPropertiesSection />} </div>
  )
}

export default RenderFeaturedPropertiesSection