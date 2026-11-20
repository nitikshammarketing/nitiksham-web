import React,{useEffect,useState,useCallback} from 'react';
import{createRoot}from'react-dom/client';
import './studio.css';

const api=async(path,opt={},token='')=>{
  const headers={...(opt.headers||{})};
  if(opt.method && !['GET','HEAD'].includes(opt.method.toUpperCase()))headers['X-CSRF-Token']=token;
  const r=await fetch('/api'+path,{...opt,headers,credentials:'same-origin'});
  const data=await r.json().catch(()=>({error:'Invalid server response'}));
  if(!r.ok)throw Error(data.error||'Request failed');
  return data;
};
function App(){
  const[session,setSession]=useState(null),[items,setItems]=useState([]),[tab,setTab]=useState('gallery'),[error,setError]=useState(''),[saving,setSaving]=useState(false);
  const csrf=document.querySelector('meta[name="nsm-csrf"]')?.content||'';
  const refresh=useCallback(async()=>{try{setItems(await api('/manage/items'));setError('')}catch(e){setError(e.message)}},[]);
  useEffect(()=>{api('/session').then(s=>{setSession(s);if(s.owner)refresh()}).catch(e=>setError(e.message))},[refresh]);
  async function submit(e,type){
    e.preventDefault();setSaving(true);
    try{
      const form=e.currentTarget;
      if(type==='gallery'){
        const data=new FormData(form);data.set('published',String(form.elements.published.checked));
        await api('/manage/upload',{method:'POST',body:data},csrf);
      }else{
        const data=Object.fromEntries(new FormData(form));
        data.published=form.elements.published.checked;
        await api('/manage/software',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)},csrf);
      }
      form.reset();await refresh();
    }catch(e){setError(e.message)}finally{setSaving(false)}
  }
  async function patch(id,data){try{await api('/manage/items/'+id,{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)},csrf);await refresh()}catch(e){setError(e.message)}}
  if(!session)return <p className="status">Checking Studio access…</p>;
  if(!session.owner)return <p className="status">Private workspace. <a href="/login/">Sign in</a> to continue.</p>;
  return <main className="nsm-app">
    <header><small>PRIVATE WORKSPACE</small><h1>Studio desk<span>.</span></h1><p>Signed in as {session.email}</p></header>
    <nav><button className={tab==='gallery'?'active':''} onClick={()=>setTab('gallery')}>Gallery & newsletters</button><button className={tab==='software'?'active':''} onClick={()=>setTab('software')}>Software & demos</button></nav>
    {error&&<p role="alert" className="error">{error}</p>}
    <div className="columns">
    {tab==='gallery'?<form onSubmit={e=>submit(e,'gallery')} className="panel">
      <h2>Upload a design or newsletter</h2>
      <label>Title<input name="title" maxLength={120} required/></label>
      <label>Category<select name="kind"><option value="design">Design</option><option value="newsletter">Newsletter</option></select></label>
      <label>Description<textarea name="description" maxLength={1000}/></label>
      <label>File<input type="file" name="file" accept="image/png,image/jpeg,image/webp,application/pdf" required/></label>
      <label><input name="published" type="checkbox"/> Publish</label>
      <button disabled={saving}>Upload</button>
    </form>:<form onSubmit={e=>submit(e,'software')} className="panel">
      <h2>Add a software project</h2>
      <label>Name<input name="title" maxLength={120} required/></label>
      <label>Description<textarea name="description" maxLength={1000}/></label>
      <label>Live URL<input name="liveUrl" type="url"/></label>
      <label>Demo URL<input name="demoUrl" type="url"/></label>
      <label><input name="published" type="checkbox"/> Publish</label>
      <button disabled={saving}>Save</button>
    </form>}
    <section className="collection"><h2>Your collection</h2>
      {items.filter(x=>!x.archived&&(tab==='gallery'?x.kind!=='software':x.kind==='software')).map(x=><article key={x.id} className="item">
       {x.mime?.startsWith('image/')&&<img src={'/api/file/'+x.id} alt={x.title}/>}
       <small>{x.kind.toUpperCase()} / {x.published?'PUBLIC':'PRIVATE'}</small><h3>{x.title}</h3><p>{x.description}</p>
       <button onClick={()=>patch(x.id,{published:!x.published})}>{x.published?'Unpublish':'Publish'}</button>
       <button onClick={()=>patch(x.id,{archived:true})}>Archive</button>
      </article>)}
      {!items.length&&<p>No items yet.</p>}
    </section></div>
  </main>
}
createRoot(document.getElementById('root')).render(<App/>);