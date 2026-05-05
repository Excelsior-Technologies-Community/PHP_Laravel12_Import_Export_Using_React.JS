import React, { useState, useEffect } from "react";
import { createRoot } from "react-dom/client";
import toast, { Toaster } from "react-hot-toast";

function ProductForm({ product, onBack }) {
  const [name, setName] = useState(product?.name ?? "");
  const [description, setDescription] = useState(product?.description ?? "");
  const [price, setPrice] = useState(product?.price ?? "");
  const [status, setStatus] = useState(product?.status ?? "active");

  return (
    <div className="card shadow-sm p-4 border-0">
      <button className="btn btn-outline-secondary btn-sm mb-3" style={{ width: 'fit-content' }} onClick={onBack}>
        ← Back
      </button>
      <form method="POST" action={product ? `/products/${product.id}/update` : "/products/store"}>
        <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').content} />
        <h3 className="fw-bold mb-4">{product ? "Edit Product" : "Create Product"}</h3>
        <div className="mb-3">
          <label className="form-label small fw-bold">Name</label>
          <input type="text" name="name" value={name} onChange={(e) => setName(e.target.value)} className="form-control" required />
        </div>
        <div className="mb-3">
          <label className="form-label small fw-bold">Description</label>
          <textarea name="description" value={description} onChange={(e) => setDescription(e.target.value)} className="form-control" rows="2" />
        </div>
        <div className="row">
          <div className="col-md-6 mb-3">
            <label className="form-label small fw-bold">Price (₹)</label>
            <input type="number" name="price" value={price} onChange={(e) => setPrice(e.target.value)} className="form-control" required />
          </div>
          <div className="col-md-6 mb-3">
            <label className="form-label small fw-bold">Status</label>
            <select name="status" value={status} onChange={(e) => setStatus(e.target.value)} className="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <button className="btn btn-primary w-100 mt-2">{product ? "Update" : "Save"}</button>
      </form>
    </div>
  );
}

function ProductIndex({ products }) {
  const [list, setList] = useState(products);
  const [search, setSearch] = useState("");
  const [selectedIds, setSelectedIds] = useState([]);
  const [statusFilter, setStatusFilter] = useState("all");
  const [maxPrice, setMaxPrice] = useState(50000);
  const [editingProduct, setEditingProduct] = useState(null);
  const [addingProduct, setAddingProduct] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 5;

  const filteredList = list.filter((p) => {
    const matchesSearch = p.name.toLowerCase().includes(search.toLowerCase());
    const matchesStatus = statusFilter === "all" || p.status === statusFilter;
    const matchesPrice = parseFloat(p.price) <= maxPrice;
    return matchesSearch && matchesStatus && matchesPrice;
  });

  const totalPages = Math.ceil(filteredList.length / itemsPerPage);
  const paginatedList = filteredList.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

  const toggleSelect = (id) => {
    setSelectedIds(prev => prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]);
  };

  const handleExportSelected = (type) => {
    if (selectedIds.length === 0) return toast.error("Please select products first");
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = type === 'excel' ? '/products/export-selected' : '/products/export-pdf';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfInput);

    selectedIds.forEach(id => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = id;
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
  };

  const handleAction = async (id, action) => {
    const res = await fetch(`/products/${id}/${action}`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    if (res.ok) {
      toast.success(`Done!`);
      window.location.reload();
    }
  };

  if (addingProduct) return <ProductForm product={null} onBack={() => setAddingProduct(false)} />;
  if (editingProduct) return <ProductForm product={editingProduct} onBack={() => setEditingProduct(null)} />;

  return (
    <div>
      <Toaster />
      <div className="row mb-3 g-2">
        <div className="col-md-4">
          <input type="text" placeholder="Search..." className="form-control" value={search} onChange={(e) => setSearch(e.target.value)} />
        </div>
        <div className="col-md-2">
          <select className="form-select" onChange={(e) => setStatusFilter(e.target.value)}>
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div className="col-md-6 d-flex gap-2 justify-content-end">
          <button className="btn btn-success btn-sm" onClick={() => handleExportSelected('excel')}>Excel Selected</button>
          <button className="btn btn-danger btn-sm" onClick={() => handleExportSelected('pdf')}>PDF Selected</button>
          <button className="btn btn-primary btn-sm" onClick={() => setAddingProduct(true)}>+ Add</button>
        </div>
      </div>

      <div className="table-responsive bg-white rounded shadow-sm">
        <table className="table table-hover align-middle mb-0">
          <thead className="table-dark">
            <tr>
              <th><input type="checkbox" onChange={(e) => setSelectedIds(e.target.checked ? paginatedList.map(p => p.id) : [])} /></th>
              <th>Name</th>
              <th>Price</th>
              <th>Status</th>
              <th className="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            {paginatedList.length > 0 ? paginatedList.map((p) => (
              <tr key={p.id}>
                <td><input type="checkbox" checked={selectedIds.includes(p.id)} onChange={() => toggleSelect(p.id)} /></td>
                <td>{p.name}</td>
                <td>₹{p.price}</td>
                <td><span className={`badge bg-${p.status === 'active' ? 'success' : 'warning'}`}>{p.status}</span></td>
                <td className="text-center">
                  <button className="btn btn-sm btn-info me-1" onClick={() => setEditingProduct(p)}>Edit</button>
                  <button className="btn btn-sm btn-danger" onClick={() => handleAction(p.id, 'delete')}>Delete</button>
                </td>
              </tr>
            )) : <tr><td colSpan="5" className="text-center py-3">No products found</td></tr>}
          </tbody>
        </table>
      </div>

      <div className="d-flex justify-content-between mt-3">
        <button className="btn btn-light btn-sm border" disabled={currentPage === 1} onClick={() => setCurrentPage(prev => prev - 1)}>Prev</button>
        <span>{currentPage} / {totalPages || 1}</span>
        <button className="btn btn-light btn-sm border" disabled={currentPage === totalPages} onClick={() => setCurrentPage(prev => prev + 1)}>Next</button>
      </div>
    </div>
  );
}

const root = document.getElementById("app");
if (root) createRoot(root).render(<ProductIndex products={window.productsData || []} />);