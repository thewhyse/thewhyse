!window.wp.blockEditor&&window.wp.blocks&&window.wp.oldEditor&&(window.wp.blockEditor=window.wp.editor);const n=()=>document.body.classList.contains("block-editor-page")&&window.wp.data&&t(),e=()=>!!(document.querySelector("#wp-content-wrap.tmce-active")||document.querySelector("#wp-content-wrap.html-active")),r=()=>!!(document.body.classList.contains("elementor-editor-active")&&window.elementor),i=()=>!!(document.body.classList.contains("et_pb_pagebuilder_layout")&&window.ET_Builder),s=()=>!!(document.body.classList.contains("seedprod-builder")&&window.seedprod_data),c=()=>window.aioseo.data.isWooCommerceActive&&window.aioseo.currentPost&&window.aioseo.currentPost.postType==="product",t=()=>{const o=window.wp;return typeof o!="undefined"&&typeof o.blocks!="undefined"&&typeof o.blockEditor!="undefined"};export{e as a,r as b,i as c,s as d,c as e,t as f,n as i};