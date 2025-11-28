import React from 'react'
import {Filter} from '../Filter'
import { Link } from 'react-router-dom'

function NewsAndUpdates() {
    return (
        <>
            <div className={`card`}>
                <Filter />
                <div className={`card-body pb-0`}>
                    <h5 className={`card-title`}>News &amp; Updates <span>| Today</span></h5>
                    <div className={`news`}>
                        <div className={`post-item clearfix`}>
                            <img src="assets/img/news-1.jpg" alt="" loading={`lazy`} />
                            <h4><Link to={`#`} onClick={(e) => e.preventDefault()}>Nihil blanditiis at in nihil autem</Link></h4>
                            <p>Sit recusandae non aspernatur laboriosam. Quia enim eligendi sed ut harum...</p>
                        </div>
                        <div className={`post-item clearfix`}>
                            <img src="assets/img/news-2.jpg" alt="" loading={`lazy`} />
                            <h4><Link to={`#`} onClick={(e) => e.preventDefault()}>Quidem autem et impedit</Link></h4>
                            <p>Illo nemo neque maiores vitae officiis cum eum turos elan dries werona nande...</p>
                        </div>
                        <div className={`post-item clearfix`}>
                            <img src="assets/img/news-3.jpg" alt="" loading={`lazy`} />
                            <h4><Link to={`#`} onClick={(e) => e.preventDefault()}>Id quia et et ut maxime similique occaecati ut</Link></h4>
                            <p>Fugiat voluptas vero eaque accusantium eos. Consequuntur sed ipsam et totam...</p>
                        </div>
                        <div className={`post-item clearfix`}>
                            <img src="assets/img/news-4.jpg" alt="" loading={`lazy`} />
                            <h4><Link to={`#`} onClick={(e) => e.preventDefault()}>Laborum corporis quo dara net para</Link></h4>
                            <p>Qui enim quia optio. Eligendi aut asperiores enim repellendusvel rerum cuder...</p>
                        </div>
                        <div className={`post-item clearfix`}>
                            <img src="assets/img/news-5.jpg" alt="" loading={`lazy`} />
                            <h4><Link to={`#`} onClick={(e) => e.preventDefault()}>Et dolores corrupti quae illo quod dolor</Link></h4>
                            <p>Odit ut eveniet modi reiciendis. Atque cupiditate libero beatae dignissimos eius...</p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
}

export default NewsAndUpdates