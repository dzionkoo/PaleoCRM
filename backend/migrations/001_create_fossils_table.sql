-- PaleoCRM: fossils table migration
-- Supports SQLite (dev) and PostgreSQL (prod)

CREATE TABLE IF NOT EXISTS fossils (
    id                TEXT        PRIMARY KEY,
    species           TEXT        NOT NULL,
    collection_name   TEXT        NOT NULL,
    estimated_age     INTEGER     NOT NULL,
    weight            REAL        NOT NULL,
    status            TEXT        NOT NULL DEFAULT 'documented',
    discovery_location TEXT       NOT NULL DEFAULT 'Unknown',
    ai_description    TEXT,
    metadata          TEXT,       -- JSON blob
    created_at        TEXT        NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now'))
);

-- Indexes for common query patterns
CREATE INDEX IF NOT EXISTS idx_fossils_status      ON fossils (status);
CREATE INDEX IF NOT EXISTS idx_fossils_species     ON fossils (species);
CREATE INDEX IF NOT EXISTS idx_fossils_estimated_age ON fossils (estimated_age DESC);

-- Seed data for local development / demo
INSERT OR IGNORE INTO fossils
    (id, species, collection_name, estimated_age, weight, status, discovery_location, ai_description)
VALUES
    ('FOSSIL_001', 'Tyrannosaurus rex',  'Hell Creek Formation Collection', 66,  9000,  'documented',       'Hell Creek Formation',   NULL),
    ('FOSSIL_002', 'Velociraptor',       'Deinonychus Study Collection',    75,  15,    'extinct',          'Paleocene Valley',       NULL),
    ('FOSSIL_003', 'Triceratops',        'Horned Dinosaur Archive',         68,  6000,  'documented',       'Lance Formation',        NULL),
    ('FOSSIL_004', 'Stegosaurus',        'Plated Dinosaur Collection',      150, 2700,  'pending-analysis', 'Morrison Formation',     NULL),
    ('FOSSIL_005', 'Brachiosaurus',      'Sauropod Exhibit',                155, 56000, 'active',           'Western Interior Basin', NULL),
    ('FOSSIL_006', 'Archaeopteryx',      'Transitional Species Archive',    150, 1,     'documented',       'Solnhofen Limestone',    NULL),
    ('FOSSIL_007', 'Spinosaurus',        'African Cretaceous Collection',   95,  7000,  'documented',       'Kem Kem Beds',           NULL),
    ('FOSSIL_008', 'Ankylosaurus',       'Armored Dinosaur Repository',     66,  6000,  'active',           'Two Medicine Formation', NULL);