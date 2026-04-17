import React, { useState } from "react";
import { createRoot } from "react-dom/client";

/**
 * ProductForm Component
 */
function ProductForm({ product, onBack }) {
  const [name, setName] = useState(product?.name ?? "");
  const [description, setDescription] = useState(product?.description ?? "");
  const [price, setPrice] = useState(product?.price ?? "");
  const [status, setStatus] = useState(product?.status ?? "active");

  return (
    <div>
      <button className="btn btn-secondary mb-3" onClick={onBack}>
        ← Back
      </button>

      <form
        method="POST"
        action={product ? `/products/${product.id}/update` : "/products/store"}
      >
        <input
          type="hidden"
          name="_token"
          value={document.querySelector('meta[name="csrf-token"]').content}
        />

        <h3>{product ? "Edit Product" : "Add Product"}</h3>

        <input
          type="text"
          name="name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          className="form-control mb-2"
          required
        />

        <textarea
          name="description"
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          className="form-control mb-2"
        />

        <input
          type="number"
          name="price"
          value={price}
          onChange={(e) => setPrice(e.target.value)}
          className="form-control mb-2"
          required
        />

        <select
          name="status"
          value={status}
          onChange={(e) => setStatus(e.target.value)}
          className="form-control mb-3"
        >
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

        <button className="btn btn-primary">
          {product ? "Update" : "Save"}
        </button>
      </form>
    </div>
  );
}

/**
 * ProductIndex Component
 */
function ProductIndex({ products }) {
  const [list, setList] = useState(products);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [editingProduct, setEditingProduct] = useState(null);
  const [addingProduct, setAddingProduct] = useState(false);

  // ✅ PAGINATION STATE
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 4;

  // FILTER
  const filteredList = list.filter((p) => {
    const searchText = search.toLowerCase();

    return (
      (p.name.toLowerCase().includes(searchText) ||
        (p.description ?? "").toLowerCase().includes(searchText) ||
        String(p.price).includes(searchText)) &&
      (statusFilter === "all" || p.status === statusFilter)
    );
  });

  // ✅ PAGINATION LOGIC
  const totalPages = Math.ceil(filteredList.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const paginatedList = filteredList.slice(
    startIndex,
    startIndex + itemsPerPage
  );

  const handleDelete = (id) => {
    if (!confirm("Delete this product?")) return;

    setList(list.filter((p) => p.id !== id));
  };

  if (addingProduct) {
    return <ProductForm product={null} onBack={() => setAddingProduct(false)} />;
  }

  if (editingProduct) {
    return (
      <ProductForm
        product={editingProduct}
        onBack={() => setEditingProduct(null)}
      />
    );
  }

  return (
    <div>
      {/* SEARCH + FILTER */}
      <div className="row mb-3">
        <div className="col-md-4">
          <input
            type="text"
            placeholder="Search..."
            className="form-control"
            onChange={(e) => {
              setSearch(e.target.value);
              setCurrentPage(1); // reset page
            }}
          />
        </div>

        <div className="col-md-3">
          <select
            className="form-control"
            onChange={(e) => {
              setStatusFilter(e.target.value);
              setCurrentPage(1); // reset page
            }}
          >
            <option value="all">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div className="col-md-3">
          <button
            className="btn btn-primary"
            onClick={() => setAddingProduct(true)}
          >
            Add Product
          </button>
        </div>
      </div>

      {/* TABLE */}
      <table className="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
          {paginatedList.length > 0 ? (
            paginatedList.map((p) => (
              <tr key={p.id}>
                <td>{p.id}</td>
                <td>{p.name}</td>
                <td>{p.description}</td>
                <td>{p.price}</td>
                <td>{p.status}</td>
                <td>
                  <button
                    className="btn btn-warning btn-sm me-2"
                    onClick={() => setEditingProduct(p)}
                  >
                    Edit
                  </button>

                  <button
                    className="btn btn-danger btn-sm"
                    onClick={() => handleDelete(p.id)}
                  >
                    Delete
                  </button>
                </td>
              </tr>
            ))
          ) : (
            <tr>
              <td colSpan="5" className="text-center">
                No Data
              </td>
            </tr>
          )}
        </tbody>
      </table>

      {/* ✅ PAGINATION BUTTONS */}
      <div className="d-flex justify-content-center mt-3 gap-2">
        <button
          className="btn btn-secondary"
          disabled={currentPage === 1}
          onClick={() => setCurrentPage(currentPage - 1)}
        >
          Prev
        </button>

        <span className="align-self-center">
          Page {currentPage} of {totalPages}
        </span>

        <button
          className="btn btn-secondary"
          disabled={currentPage === totalPages}
          onClick={() => setCurrentPage(currentPage + 1)}
        >
          Next
        </button>
      </div>
    </div>
  );
}

/**
 * MOUNT
 */
const container = document.getElementById("app");

if (container) {
  const root = createRoot(container);
  root.render(<ProductIndex products={window.productsData || []} />);
}