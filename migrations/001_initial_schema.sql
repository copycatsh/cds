CREATE TYPE delivery_type_enum AS ENUM ('water', 'land', 'air');
CREATE TYPE order_status_enum AS ENUM ('pending', 'processing', 'delivered', 'cancelled');

CREATE TABLE clients (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(255) NOT NULL,
    phone      VARCHAR(50),
    address    TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE orders (
    id               SERIAL PRIMARY KEY,
    client_id        INT NOT NULL REFERENCES clients(id),
    delivery_type    delivery_type_enum NOT NULL,
    status           order_status_enum NOT NULL DEFAULT 'pending',
    tracking_code    VARCHAR(20) UNIQUE NOT NULL,
    delivery_address TEXT NOT NULL,
    cost             NUMERIC(10, 2),
    created_at       TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at       TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_orders_client_id ON orders(client_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_clients_phone ON clients(phone);
