--
-- The Postgres docker will create the postgres user and database
--
-- This script will
--
-- 1. Set database options
-- Taken from a Postgres 12.3 dump after it was loaded from mySQL cftf database with pg_loader
-- Dumped by pg_dump version 12.3
--
SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- 2. Create the cftf role
--
DO
$do$
BEGIN
    IF NOT EXISTS (
        SELECT FROM pg_catalog.pg_roles  -- SELECT list can be empty for this
        WHERE  rolname = 'cftf') THEN
        CREATE ROLE cftf WITH CREATEDB LOGIN ENCRYPTED PASSWORD 'cftf';
    END IF;
END
$do$;


--
-- 3. Assign ownership and all priviledges for the cftf database to the cftf role
--

CREATE DATABASE cftf;
ALTER DATABASE cftf OWNER TO cftf;
GRANT ALL PRIVILEGES ON DATABASE cftf TO cftf;

-- 
-- Then connect to the cftf database, configure database options and create the schema
--
\connect cftf

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: cftf; Type: SCHEMA; Schema: -; Owner: cftf
--

CREATE SCHEMA cftf;


ALTER SCHEMA cftf OWNER TO cftf;

SET default_tablespace = '';

SET default_table_access_method = heap;

-- 
-- 4. Execute the DDL converted from the MySQL cftf database
--

--
-- Name: audit_ls_association; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_ls_association (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    ls_doc_id bigint,
    assoc_group_id bigint,
    origin_lsdoc_id bigint,
    origin_lsitem_id bigint,
    destination_lsdoc_id bigint,
    destination_lsitem_id bigint,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone,
    changed_at timestamp with time zone,
    ls_doc_identifier character varying(300),
    ls_doc_uri character varying(300),
    origin_node_identifier character varying(300),
    origin_node_uri character varying(300),
    destination_node_identifier character varying(300),
    destination_node_uri character varying(300),
    type character varying(50),
    seq bigint,
    revtype character varying(4) NOT NULL,
    subtype character varying(255),
    annotation text
);


ALTER TABLE cftf.audit_ls_association OWNER TO cftf;

--
-- Name: COLUMN audit_ls_association.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_association.extra IS '(DC2Type:json)';


--
-- Name: COLUMN audit_ls_association.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_association.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_ls_association.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_association.changed_at IS '(DC2Type:datetime)';


--
-- Name: audit_ls_def_association_grouping; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_ls_def_association_grouping (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    ls_doc_id bigint,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone,
    changed_at timestamp with time zone,
    title character varying(1024),
    description text,
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_ls_def_association_grouping OWNER TO cftf;

--
-- Name: COLUMN audit_ls_def_association_grouping.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_association_grouping.extra IS '(DC2Type:json)';


--
-- Name: COLUMN audit_ls_def_association_grouping.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_association_grouping.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_ls_def_association_grouping.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_association_grouping.changed_at IS '(DC2Type:datetime)';


--
-- Name: audit_ls_def_concept; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_ls_def_concept (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone,
    changed_at timestamp with time zone,
    title character varying(1024),
    description text,
    hierarchy_code character varying(255),
    keywords text,
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_ls_def_concept OWNER TO cftf;

--
-- Name: COLUMN audit_ls_def_concept.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_concept.extra IS '(DC2Type:json)';


--
-- Name: COLUMN audit_ls_def_concept.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_concept.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_ls_def_concept.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_concept.changed_at IS '(DC2Type:datetime)';


--
-- Name: audit_ls_def_grade; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_ls_def_grade (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone,
    changed_at timestamp with time zone,
    title character varying(1024),
    description text,
    code character varying(255),
    rank bigint,
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_ls_def_grade OWNER TO cftf;

--
-- Name: COLUMN audit_ls_def_grade.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_grade.extra IS '(DC2Type:json)';


--
-- Name: COLUMN audit_ls_def_grade.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_grade.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_ls_def_grade.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_grade.changed_at IS '(DC2Type:datetime)';


--
-- Name: audit_ls_def_item_type; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_ls_def_item_type (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone,
    changed_at timestamp with time zone,
    title character varying(1024),
    description text,
    code character varying(255),
    hierarchy_code character varying(255),
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_ls_def_item_type OWNER TO cftf;

--
-- Name: COLUMN audit_ls_def_item_type.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_item_type.extra IS '(DC2Type:json)';


--
-- Name: COLUMN audit_ls_def_item_type.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_item_type.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_ls_def_item_type.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_item_type.changed_at IS '(DC2Type:datetime)';


--
-- Name: audit_ls_def_licence; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_ls_def_licence (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone,
    changed_at timestamp with time zone,
    title character varying(1024),
    description text,
    licence_text text,
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_ls_def_licence OWNER TO cftf;

--
-- Name: COLUMN audit_ls_def_licence.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_licence.extra IS '(DC2Type:json)';


--
-- Name: COLUMN audit_ls_def_licence.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_licence.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_ls_def_licence.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_licence.changed_at IS '(DC2Type:datetime)';


--
-- Name: audit_ls_def_subject; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_ls_def_subject (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone,
    changed_at timestamp with time zone,
    title character varying(1024),
    description text,
    hierarchy_code character varying(255),
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_ls_def_subject OWNER TO cftf;

--
-- Name: COLUMN audit_ls_def_subject.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_subject.extra IS '(DC2Type:json)';


--
-- Name: COLUMN audit_ls_def_subject.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_subject.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_ls_def_subject.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_def_subject.changed_at IS '(DC2Type:datetime)';


--
-- Name: audit_ls_doc; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_ls_doc (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    org_id bigint,
    user_id bigint,
    licence_id bigint,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb default '{}'::jsonb,
    updated_at timestamp with time zone,
    changed_at timestamp with time zone,
    official_uri character varying(300),
    creator character varying(300),
    publisher character varying(50),
    title character varying(120),
    url_name character varying(255),
    version character varying(50),
    description character varying(300),
    subject jsonb,
    language character varying(10),
    adoption_status character varying(50),
    status_start date,
    status_end date,
    note text,
    revtype character varying(4) NOT NULL,
    frameworktype_id bigint,
    mirrored_framework_id bigint
);


ALTER TABLE cftf.audit_ls_doc OWNER TO cftf;

--
-- Name: COLUMN audit_ls_doc.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_doc.extra IS '(DC2Type:json_array)';


--
-- Name: COLUMN audit_ls_doc.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_doc.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_ls_doc.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_doc.changed_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_ls_doc.subject; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_doc.subject IS '(DC2Type:json)';


--
-- Name: audit_ls_doc_attribute; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_ls_doc_attribute (
    attribute character varying(255) NOT NULL,
    ls_doc_id bigint NOT NULL,
    rev bigint NOT NULL,
    value character varying(255),
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_ls_doc_attribute OWNER TO cftf;

--
-- Name: audit_ls_item; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_ls_item (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    ls_doc_id bigint,
    item_type_id bigint,
    item_type_text character varying(255),
    licence_id bigint,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb default '{}'::jsonb,
    updated_at timestamp with time zone,
    ls_doc_identifier character varying(300),
    ls_doc_uri character varying(300),
    human_coding_scheme character varying(50),
    list_enum_in_source character varying(20),
    full_statement text,
    abbreviated_statement text,
    concept_keywords jsonb default '{}'::jsonb,
    notes text,
    language character varying(10),
    educational_alignment character varying(300),
    alternative_label text,
    status_start date,
    status_end date,
    changed_at timestamp with time zone,
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_ls_item OWNER TO cftf;

--
-- Name: COLUMN audit_ls_item.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_item.extra IS '(DC2Type:json_array)';


--
-- Name: COLUMN audit_ls_item.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_item.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_ls_item.concept_keywords; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_item.concept_keywords IS '(DC2Type:json_array)';


--
-- Name: COLUMN audit_ls_item.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_ls_item.changed_at IS '(DC2Type:datetime)';


--
-- Name: audit_revision; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_revision (
    id bigint NOT NULL,
    "timestamp" timestamp with time zone NOT NULL,
    username character varying(255)
);


ALTER TABLE cftf.audit_revision OWNER TO cftf;

--
-- Name: COLUMN audit_revision."timestamp"; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_revision."timestamp" IS '(DC2Type:datetime)';


--
-- Name: audit_revision_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.audit_revision_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.audit_revision_id_seq OWNER TO cftf;

--
-- Name: audit_revision_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.audit_revision_id_seq OWNED BY cftf.audit_revision.id;


--
-- Name: audit_rubric; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_rubric (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone,
    changed_at timestamp with time zone,
    title text,
    description text,
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_rubric OWNER TO cftf;

--
-- Name: COLUMN audit_rubric.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_rubric.extra IS '(DC2Type:json)';


--
-- Name: COLUMN audit_rubric.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_rubric.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_rubric.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_rubric.changed_at IS '(DC2Type:datetime)';


--
-- Name: audit_rubric_criterion; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_rubric_criterion (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    ls_item_id bigint,
    rubric_id bigint,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone,
    changed_at timestamp with time zone,
    category character varying(255),
    description text,
    weight double precision,
    "position" bigint,
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_rubric_criterion OWNER TO cftf;

--
-- Name: COLUMN audit_rubric_criterion.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_rubric_criterion.extra IS '(DC2Type:json)';


--
-- Name: COLUMN audit_rubric_criterion.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_rubric_criterion.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_rubric_criterion.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_rubric_criterion.changed_at IS '(DC2Type:datetime)';


--
-- Name: audit_rubric_criterion_level; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_rubric_criterion_level (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    criterion_id bigint,
    identifier character varying(300),
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone,
    changed_at timestamp with time zone,
    description text,
    quality text,
    score double precision,
    feedback text,
    "position" bigint,
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_rubric_criterion_level OWNER TO cftf;

--
-- Name: COLUMN audit_rubric_criterion_level.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_rubric_criterion_level.extra IS '(DC2Type:json)';


--
-- Name: COLUMN audit_rubric_criterion_level.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_rubric_criterion_level.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_rubric_criterion_level.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_rubric_criterion_level.changed_at IS '(DC2Type:datetime)';


--
-- Name: audit_salt_change; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_salt_change (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    user_id bigint,
    doc_id bigint,
    changed_at timestamp with time zone,
    description character varying(2048),
    changed jsonb,
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_salt_change OWNER TO cftf;

--
-- Name: COLUMN audit_salt_change.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_salt_change.changed_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN audit_salt_change.changed; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_salt_change.changed IS '(DC2Type:json)';


--
-- Name: audit_salt_org; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_salt_org (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    name character varying(255),
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_salt_org OWNER TO cftf;

--
-- Name: audit_salt_user; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_salt_user (
    id bigint NOT NULL,
    rev bigint NOT NULL,
    org_id bigint,
    username character varying(255),
    password character varying(255),
    roles jsonb default '{}'::jsonb,
    github_token character varying(40),
    revtype character varying(4) NOT NULL,
    status bigint DEFAULT '2'::bigint NOT NULL
);


ALTER TABLE cftf.audit_salt_user OWNER TO cftf;

--
-- Name: COLUMN audit_salt_user.roles; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.audit_salt_user.roles IS '(DC2Type:json_array)';


--
-- Name: audit_salt_user_doc_acl; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.audit_salt_user_doc_acl (
    id bigint NOT NULL,
    user_id bigint,
    doc_id bigint,
    rev bigint NOT NULL,
    access smallint,
    revtype character varying(4) NOT NULL
);


ALTER TABLE cftf.audit_salt_user_doc_acl OWNER TO cftf;

--
-- Name: auth_session; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.auth_session (
    id bytea NOT NULL,
    sess_data bytea NOT NULL,
    sess_time bigint NOT NULL,
    sess_lifetime bigint NOT NULL
);


ALTER TABLE cftf.auth_session OWNER TO cftf;

--
-- Name: cache_items; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.cache_items (
    item_id bytea NOT NULL,
    item_data bytea NOT NULL,
    item_lifetime bigint,
    item_time bigint NOT NULL
);


ALTER TABLE cftf.cache_items OWNER TO cftf;

--
-- Name: framework_type; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.framework_type (
    id bigint NOT NULL,
    framework_type character varying(255) NOT NULL
);


ALTER TABLE cftf.framework_type OWNER TO cftf;

--
-- Name: framework_type_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.framework_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.framework_type_id_seq OWNER TO cftf;

--
-- Name: framework_type_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.framework_type_id_seq OWNED BY cftf.framework_type.id;


--
-- Name: import_logs; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.import_logs (
    id bigint NOT NULL,
    ls_doc_id bigint NOT NULL,
    message_text character varying(250) NOT NULL,
    message_type character varying(30) NOT NULL,
    is_read boolean DEFAULT 'f' NOT NULL
);


ALTER TABLE cftf.import_logs OWNER TO cftf;

--
-- Name: import_logs_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.import_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.import_logs_id_seq OWNER TO cftf;

--
-- Name: import_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.import_logs_id_seq OWNED BY cftf.import_logs.id;


--
-- Name: ls_association; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_association (
    id bigint NOT NULL,
    identifier character varying(300) NOT NULL,
    uri character varying(300),
    ls_doc_identifier character varying(300) NOT NULL,
    ls_doc_uri character varying(300),
    ls_doc_id bigint,
    origin_node_identifier character varying(300) NOT NULL,
    origin_node_uri character varying(300),
    origin_lsdoc_id bigint,
    origin_lsitem_id bigint,
    destination_node_identifier character varying(300) NOT NULL,
    destination_node_uri character varying(300),
    destination_lsdoc_id bigint,
    destination_lsitem_id bigint,
    type character varying(50) NOT NULL,
    seq bigint,
    updated_at timestamp with time zone NOT NULL,
    changed_at timestamp with time zone NOT NULL,
    assoc_group_id bigint,
    extra jsonb,
    subtype character varying(255),
    annotation text
);


ALTER TABLE cftf.ls_association OWNER TO cftf;

--
-- Name: COLUMN ls_association.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_association.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_association.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_association.changed_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_association.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_association.extra IS '(DC2Type:json)';


--
-- Name: ls_association_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.ls_association_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.ls_association_id_seq OWNER TO cftf;

--
-- Name: ls_association_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.ls_association_id_seq OWNED BY cftf.ls_association.id;


--
-- Name: ls_def_association_grouping; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_def_association_grouping (
    id bigint NOT NULL,
    identifier character varying(300) NOT NULL,
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone NOT NULL,
    changed_at timestamp with time zone NOT NULL,
    title character varying(1024),
    description text,
    ls_doc_id bigint
);


ALTER TABLE cftf.ls_def_association_grouping OWNER TO cftf;

--
-- Name: COLUMN ls_def_association_grouping.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_association_grouping.extra IS '(DC2Type:json)';


--
-- Name: COLUMN ls_def_association_grouping.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_association_grouping.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_def_association_grouping.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_association_grouping.changed_at IS '(DC2Type:datetime)';


--
-- Name: ls_def_association_grouping_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.ls_def_association_grouping_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.ls_def_association_grouping_id_seq OWNER TO cftf;

--
-- Name: ls_def_association_grouping_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.ls_def_association_grouping_id_seq OWNED BY cftf.ls_def_association_grouping.id;


--
-- Name: ls_def_concept; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_def_concept (
    id bigint NOT NULL,
    identifier character varying(300) NOT NULL,
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone NOT NULL,
    changed_at timestamp with time zone NOT NULL,
    title character varying(1024),
    description text,
    hierarchy_code character varying(255) NOT NULL,
    keywords text
);


ALTER TABLE cftf.ls_def_concept OWNER TO cftf;

--
-- Name: COLUMN ls_def_concept.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_concept.extra IS '(DC2Type:json)';


--
-- Name: COLUMN ls_def_concept.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_concept.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_def_concept.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_concept.changed_at IS '(DC2Type:datetime)';


--
-- Name: ls_def_concept_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.ls_def_concept_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.ls_def_concept_id_seq OWNER TO cftf;

--
-- Name: ls_def_concept_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.ls_def_concept_id_seq OWNED BY cftf.ls_def_concept.id;


--
-- Name: ls_def_grade; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_def_grade (
    id bigint NOT NULL,
    identifier character varying(300) NOT NULL,
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone NOT NULL,
    changed_at timestamp with time zone NOT NULL,
    title character varying(1024),
    description text,
    code character varying(255) NOT NULL,
    rank bigint
);


ALTER TABLE cftf.ls_def_grade OWNER TO cftf;

--
-- Name: COLUMN ls_def_grade.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_grade.extra IS '(DC2Type:json)';


--
-- Name: COLUMN ls_def_grade.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_grade.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_def_grade.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_grade.changed_at IS '(DC2Type:datetime)';


--
-- Name: ls_def_grade_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.ls_def_grade_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.ls_def_grade_id_seq OWNER TO cftf;

--
-- Name: ls_def_grade_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.ls_def_grade_id_seq OWNED BY cftf.ls_def_grade.id;


--
-- Name: ls_def_item_type; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_def_item_type (
    id bigint NOT NULL,
    identifier character varying(300) NOT NULL,
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone NOT NULL,
    changed_at timestamp with time zone NOT NULL,
    title character varying(1024),
    description text,
    code character varying(255),
    hierarchy_code character varying(255) NOT NULL
);


ALTER TABLE cftf.ls_def_item_type OWNER TO cftf;

--
-- Name: COLUMN ls_def_item_type.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_item_type.extra IS '(DC2Type:json)';


--
-- Name: COLUMN ls_def_item_type.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_item_type.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_def_item_type.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_item_type.changed_at IS '(DC2Type:datetime)';


--
-- Name: ls_def_item_type_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.ls_def_item_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.ls_def_item_type_id_seq OWNER TO cftf;

--
-- Name: ls_def_item_type_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.ls_def_item_type_id_seq OWNED BY cftf.ls_def_item_type.id;


--
-- Name: ls_def_licence; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_def_licence (
    id bigint NOT NULL,
    identifier character varying(300) NOT NULL,
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone NOT NULL,
    changed_at timestamp with time zone NOT NULL,
    title character varying(1024),
    description text,
    licence_text text NOT NULL
);


ALTER TABLE cftf.ls_def_licence OWNER TO cftf;

--
-- Name: COLUMN ls_def_licence.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_licence.extra IS '(DC2Type:json)';


--
-- Name: COLUMN ls_def_licence.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_licence.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_def_licence.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_licence.changed_at IS '(DC2Type:datetime)';


--
-- Name: ls_def_licence_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.ls_def_licence_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.ls_def_licence_id_seq OWNER TO cftf;

--
-- Name: ls_def_licence_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.ls_def_licence_id_seq OWNED BY cftf.ls_def_licence.id;


--
-- Name: ls_def_subject; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_def_subject (
    id bigint NOT NULL,
    identifier character varying(300) NOT NULL,
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone NOT NULL,
    changed_at timestamp with time zone NOT NULL,
    title character varying(1024),
    description text,
    hierarchy_code character varying(255) NOT NULL
);


ALTER TABLE cftf.ls_def_subject OWNER TO cftf;

--
-- Name: COLUMN ls_def_subject.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_subject.extra IS '(DC2Type:json)';


--
-- Name: COLUMN ls_def_subject.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_subject.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_def_subject.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_def_subject.changed_at IS '(DC2Type:datetime)';


--
-- Name: ls_def_subject_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.ls_def_subject_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.ls_def_subject_id_seq OWNER TO cftf;

--
-- Name: ls_def_subject_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.ls_def_subject_id_seq OWNED BY cftf.ls_def_subject.id;


--
-- Name: ls_doc; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_doc (
    id bigint NOT NULL,
    org_id bigint,
    user_id bigint,
    uri character varying(300),
    identifier character varying(300) NOT NULL,
    official_uri character varying(300),
    creator character varying(300) NOT NULL,
    publisher character varying(50),
    title character varying(120) NOT NULL,
    version character varying(50),
    description character varying(300),
    subject jsonb,
    language character varying(10),
    adoption_status character varying(50),
    status_start date,
    status_end date,
    note text,
    updated_at timestamp with time zone NOT NULL,
    changed_at timestamp with time zone NOT NULL,
    url_name character varying(255),
    licence_id bigint,
    extra jsonb default '{}'::jsonb,
    frameworktype_id bigint,
    mirrored_framework_id bigint
);


ALTER TABLE cftf.ls_doc OWNER TO cftf;

--
-- Name: COLUMN ls_doc.subject; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_doc.subject IS '(DC2Type:json)';


--
-- Name: COLUMN ls_doc.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_doc.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_doc.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_doc.changed_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_doc.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_doc.extra IS '(DC2Type:json_array)';


--
-- Name: ls_doc_attribute; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_doc_attribute (
    ls_doc_id bigint NOT NULL,
    attribute character varying(255) NOT NULL,
    value character varying(255)
);


ALTER TABLE cftf.ls_doc_attribute OWNER TO cftf;

--
-- Name: ls_doc_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.ls_doc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.ls_doc_id_seq OWNER TO cftf;

--
-- Name: ls_doc_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.ls_doc_id_seq OWNED BY cftf.ls_doc.id;


--
-- Name: ls_doc_subject; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_doc_subject (
    ls_doc_id bigint NOT NULL,
    subject_id bigint NOT NULL
);


ALTER TABLE cftf.ls_doc_subject OWNER TO cftf;

--
-- Name: ls_item; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_item (
    id bigint NOT NULL,
    ls_doc_identifier character varying(300) NOT NULL,
    ls_doc_id bigint,
    uri character varying(300),
    ls_doc_uri character varying(300),
    human_coding_scheme character varying(50),
    identifier character varying(300) NOT NULL,
    list_enum_in_source character varying(20),
    full_statement text NOT NULL,
    abbreviated_statement text,
    concept_keywords jsonb default '{}'::jsonb,
    notes text,
    language character varying(10),
    educational_alignment character varying(300),
    type character varying(60),
    item_type_id bigint,
    item_type_text character varying(255),
    changed_at timestamp with time zone NOT NULL,
    updated_at timestamp with time zone NOT NULL,
    extra jsonb DEFAULT '{}'::jsonb,
    licence_id bigint,
    alternative_label text,
    status_start date,
    status_end date
);


ALTER TABLE cftf.ls_item OWNER TO cftf;

--
-- Name: COLUMN ls_item.concept_keywords; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_item.concept_keywords IS '(DC2Type:json_array)';


--
-- Name: COLUMN ls_item.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_item.changed_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_item.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_item.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN ls_item.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.ls_item.extra IS '(DC2Type:json_array)';


--
-- Name: ls_item_concept; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.ls_item_concept (
    ls_item_id bigint NOT NULL,
    concept_id bigint NOT NULL
);


ALTER TABLE cftf.ls_item_concept OWNER TO cftf;

--
-- Name: ls_item_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.ls_item_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.ls_item_id_seq OWNER TO cftf;

--
-- Name: ls_item_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.ls_item_id_seq OWNED BY cftf.ls_item.id;


--
-- Name: migration_versions; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.migration_versions (
    version character varying(14) NOT NULL,
    executed_at timestamp with time zone NOT NULL
);


ALTER TABLE cftf.migration_versions OWNER TO cftf;

--
-- Name: COLUMN migration_versions.executed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.migration_versions.executed_at IS '(DC2Type:datetime_immutable)';


--
-- Name: mirror_framework; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.mirror_framework (
    id bigint NOT NULL,
    server_id bigint,
    url character varying(255) NOT NULL,
    identifier character varying(255) NOT NULL,
    creator character varying(255),
    title character varying(255),
    include SMALLINT NOT NULL,
    priority bigint DEFAULT '0'::bigint NOT NULL,
    status character varying(255) DEFAULT 'new'::character varying NOT NULL,
    status_count bigint DEFAULT '0'::bigint NOT NULL,
    last_check timestamp with time zone,
    last_success timestamp with time zone,
    last_failure timestamp with time zone,
    last_change timestamp with time zone,
    next_check timestamp with time zone,
    error_type character varying(255),
    updated_at timestamp with time zone NOT NULL,
    last_content bytea,
    last_success_content bytea
);


ALTER TABLE cftf.mirror_framework OWNER TO cftf;

--
-- Name: COLUMN mirror_framework.last_check; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.mirror_framework.last_check IS '(DC2Type:datetime)';


--
-- Name: COLUMN mirror_framework.last_success; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.mirror_framework.last_success IS '(DC2Type:datetime)';


--
-- Name: COLUMN mirror_framework.last_failure; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.mirror_framework.last_failure IS '(DC2Type:datetime)';


--
-- Name: COLUMN mirror_framework.last_change; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.mirror_framework.last_change IS '(DC2Type:datetime)';


--
-- Name: COLUMN mirror_framework.next_check; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.mirror_framework.next_check IS '(DC2Type:datetime)';


--
-- Name: COLUMN mirror_framework.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.mirror_framework.updated_at IS '(DC2Type:datetime)';


--
-- Name: mirror_framework_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.mirror_framework_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.mirror_framework_id_seq OWNER TO cftf;

--
-- Name: mirror_framework_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.mirror_framework_id_seq OWNED BY cftf.mirror_framework.id;


--
-- Name: mirror_log; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.mirror_log (
    id bigint NOT NULL,
    mirror_id bigint NOT NULL,
    status character varying(255) NOT NULL,
    message text NOT NULL,
    occurred_at timestamp with time zone NOT NULL
);


ALTER TABLE cftf.mirror_log OWNER TO cftf;

--
-- Name: COLUMN mirror_log.occurred_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.mirror_log.occurred_at IS '(DC2Type:datetime)';


--
-- Name: mirror_log_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.mirror_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.mirror_log_id_seq OWNER TO cftf;

--
-- Name: mirror_log_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.mirror_log_id_seq OWNED BY cftf.mirror_log.id;


--
-- Name: mirror_oauth; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.mirror_oauth (
    id bigint NOT NULL,
    endpoint character varying(255) NOT NULL,
    auth_key character varying(255) NOT NULL,
    auth_secret character varying(255) NOT NULL,
    updated_at timestamp with time zone NOT NULL
);


ALTER TABLE cftf.mirror_oauth OWNER TO cftf;

--
-- Name: COLUMN mirror_oauth.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.mirror_oauth.updated_at IS '(DC2Type:datetime)';


--
-- Name: mirror_oauth_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.mirror_oauth_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.mirror_oauth_id_seq OWNER TO cftf;

--
-- Name: mirror_oauth_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.mirror_oauth_id_seq OWNED BY cftf.mirror_oauth.id;


--
-- Name: mirror_server; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.mirror_server (
    id bigint NOT NULL,
    credentials_id bigint,
    url character varying(255) NOT NULL,
    api_type character varying(255) NOT NULL,
    check_server SMALLINT NOT NULL,
    add_found SMALLINT NOT NULL,
    priority bigint DEFAULT '0'::bigint NOT NULL,
    next_check timestamp with time zone,
    last_check timestamp with time zone,
    updated_at timestamp with time zone NOT NULL
);


ALTER TABLE cftf.mirror_server OWNER TO cftf;

--
-- Name: COLUMN mirror_server.next_check; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.mirror_server.next_check IS '(DC2Type:datetime)';


--
-- Name: COLUMN mirror_server.last_check; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.mirror_server.last_check IS '(DC2Type:datetime)';


--
-- Name: COLUMN mirror_server.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.mirror_server.updated_at IS '(DC2Type:datetime)';


--
-- Name: mirror_server_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.mirror_server_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.mirror_server_id_seq OWNER TO cftf;

--
-- Name: mirror_server_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.mirror_server_id_seq OWNED BY cftf.mirror_server.id;


--
-- Name: rubric; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.rubric (
    id bigint NOT NULL,
    identifier character varying(300) NOT NULL,
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone NOT NULL,
    changed_at timestamp with time zone NOT NULL,
    title text,
    description text
);


ALTER TABLE cftf.rubric OWNER TO cftf;

--
-- Name: COLUMN rubric.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.rubric.extra IS '(DC2Type:json)';


--
-- Name: COLUMN rubric.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.rubric.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN rubric.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.rubric.changed_at IS '(DC2Type:datetime)';


--
-- Name: rubric_criterion; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.rubric_criterion (
    id bigint NOT NULL,
    ls_item_id bigint,
    rubric_id bigint NOT NULL,
    identifier character varying(300) NOT NULL,
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone NOT NULL,
    changed_at timestamp with time zone NOT NULL,
    category character varying(255),
    description text,
    weight double precision,
    "position" bigint
);


ALTER TABLE cftf.rubric_criterion OWNER TO cftf;

--
-- Name: COLUMN rubric_criterion.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.rubric_criterion.extra IS '(DC2Type:json)';


--
-- Name: COLUMN rubric_criterion.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.rubric_criterion.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN rubric_criterion.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.rubric_criterion.changed_at IS '(DC2Type:datetime)';


--
-- Name: rubric_criterion_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.rubric_criterion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.rubric_criterion_id_seq OWNER TO cftf;

--
-- Name: rubric_criterion_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.rubric_criterion_id_seq OWNED BY cftf.rubric_criterion.id;


--
-- Name: rubric_criterion_level; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.rubric_criterion_level (
    id bigint NOT NULL,
    criterion_id bigint NOT NULL,
    identifier character varying(300) NOT NULL,
    uri character varying(300),
    extra jsonb,
    updated_at timestamp with time zone NOT NULL,
    changed_at timestamp with time zone NOT NULL,
    description text,
    quality text,
    score double precision,
    feedback text,
    "position" bigint
);


ALTER TABLE cftf.rubric_criterion_level OWNER TO cftf;

--
-- Name: COLUMN rubric_criterion_level.extra; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.rubric_criterion_level.extra IS '(DC2Type:json)';


--
-- Name: COLUMN rubric_criterion_level.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.rubric_criterion_level.updated_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN rubric_criterion_level.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.rubric_criterion_level.changed_at IS '(DC2Type:datetime)';


--
-- Name: rubric_criterion_level_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.rubric_criterion_level_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.rubric_criterion_level_id_seq OWNER TO cftf;

--
-- Name: rubric_criterion_level_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.rubric_criterion_level_id_seq OWNED BY cftf.rubric_criterion_level.id;


--
-- Name: rubric_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.rubric_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.rubric_id_seq OWNER TO cftf;

--
-- Name: rubric_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.rubric_id_seq OWNED BY cftf.rubric.id;


--
-- Name: salt_additional_field; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.salt_additional_field (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    applies_to character varying(255) NOT NULL,
    display_name character varying(255) NOT NULL,
    type character varying(255) NOT NULL,
    type_info jsonb
);


ALTER TABLE cftf.salt_additional_field OWNER TO cftf;

--
-- Name: COLUMN salt_additional_field.type_info; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.salt_additional_field.type_info IS '(DC2Type:json)';


--
-- Name: salt_additional_field_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.salt_additional_field_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.salt_additional_field_id_seq OWNER TO cftf;

--
-- Name: salt_additional_field_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.salt_additional_field_id_seq OWNED BY cftf.salt_additional_field.id;


--
-- Name: salt_association_subtype; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.salt_association_subtype (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    parent_type character varying(255) NOT NULL,
    direction bigint NOT NULL,
    description character varying(512) NOT NULL
);


ALTER TABLE cftf.salt_association_subtype OWNER TO cftf;

--
-- Name: salt_association_subtype_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.salt_association_subtype_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.salt_association_subtype_id_seq OWNER TO cftf;

--
-- Name: salt_association_subtype_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.salt_association_subtype_id_seq OWNED BY cftf.salt_association_subtype.id;


--
-- Name: salt_change; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.salt_change (
    id bigint NOT NULL,
    user_id bigint,
    doc_id bigint,
    changed_at timestamp with time zone NOT NULL,
    description character varying(2048) NOT NULL,
    changed jsonb
);


ALTER TABLE cftf.salt_change OWNER TO cftf;

--
-- Name: COLUMN salt_change.changed_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.salt_change.changed_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN salt_change.changed; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.salt_change.changed IS '(DC2Type:json)';


--
-- Name: salt_change_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.salt_change_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.salt_change_id_seq OWNER TO cftf;

--
-- Name: salt_change_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.salt_change_id_seq OWNED BY cftf.salt_change.id;


--
-- Name: salt_comment; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.salt_comment (
    id bigint NOT NULL,
    parent_id bigint,
    user_id bigint NOT NULL,
    content text NOT NULL,
    created_at timestamp with time zone NOT NULL,
    updated_at timestamp with time zone NOT NULL,
    document_id bigint,
    item_id bigint,
    file_url character varying(255),
    file_mime_type character varying(255)
);


ALTER TABLE cftf.salt_comment OWNER TO cftf;

--
-- Name: COLUMN salt_comment.created_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.salt_comment.created_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN salt_comment.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.salt_comment.updated_at IS '(DC2Type:datetime)';


--
-- Name: salt_comment_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.salt_comment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.salt_comment_id_seq OWNER TO cftf;

--
-- Name: salt_comment_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.salt_comment_id_seq OWNED BY cftf.salt_comment.id;


--
-- Name: salt_comment_upvote; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.salt_comment_upvote (
    id bigint NOT NULL,
    comment_id bigint NOT NULL,
    user_id bigint NOT NULL,
    created_at timestamp with time zone NOT NULL,
    updated_at timestamp with time zone NOT NULL
);


ALTER TABLE cftf.salt_comment_upvote OWNER TO cftf;

--
-- Name: COLUMN salt_comment_upvote.created_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.salt_comment_upvote.created_at IS '(DC2Type:datetime)';


--
-- Name: COLUMN salt_comment_upvote.updated_at; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.salt_comment_upvote.updated_at IS '(DC2Type:datetime)';


--
-- Name: salt_comment_upvote_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.salt_comment_upvote_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.salt_comment_upvote_id_seq OWNER TO cftf;

--
-- Name: salt_comment_upvote_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.salt_comment_upvote_id_seq OWNED BY cftf.salt_comment_upvote.id;


--
-- Name: salt_object_lock; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.salt_object_lock (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    doc_id bigint,
    expiry timestamp with time zone NOT NULL,
    obj_type character varying(255) NOT NULL,
    obj_id character varying(255) NOT NULL
);


ALTER TABLE cftf.salt_object_lock OWNER TO cftf;

--
-- Name: COLUMN salt_object_lock.expiry; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.salt_object_lock.expiry IS '(DC2Type:datetime)';


--
-- Name: salt_object_lock_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.salt_object_lock_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.salt_object_lock_id_seq OWNER TO cftf;

--
-- Name: salt_object_lock_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.salt_object_lock_id_seq OWNED BY cftf.salt_object_lock.id;


--
-- Name: salt_org; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.salt_org (
    id bigint NOT NULL,
    name character varying(255) NOT NULL
);


ALTER TABLE cftf.salt_org OWNER TO cftf;

--
-- Name: salt_org_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.salt_org_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.salt_org_id_seq OWNER TO cftf;

--
-- Name: salt_org_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.salt_org_id_seq OWNED BY cftf.salt_org.id;


--
-- Name: salt_user; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.salt_user (
    id bigint NOT NULL,
    org_id bigint NOT NULL,
    username character varying(255) NOT NULL,
    password character varying(255),
    roles jsonb default '{}'::jsonb,
    github_token character varying(40),
    status bigint DEFAULT '2'::bigint NOT NULL
);


ALTER TABLE cftf.salt_user OWNER TO cftf;

--
-- Name: COLUMN salt_user.roles; Type: COMMENT; Schema: cftf; Owner: cftf
--

COMMENT ON COLUMN cftf.salt_user.roles IS '(DC2Type:json_array)';


--
-- Name: salt_user_doc_acl; Type: TABLE; Schema: cftf; Owner: cftf
--

CREATE TABLE cftf.salt_user_doc_acl (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    doc_id bigint NOT NULL,
    access smallint NOT NULL
);


ALTER TABLE cftf.salt_user_doc_acl OWNER TO cftf;

--
-- Name: salt_user_doc_acl_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.salt_user_doc_acl_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.salt_user_doc_acl_id_seq OWNER TO cftf;

--
-- Name: salt_user_doc_acl_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.salt_user_doc_acl_id_seq OWNED BY cftf.salt_user_doc_acl.id;


--
-- Name: salt_user_id_seq; Type: SEQUENCE; Schema: cftf; Owner: cftf
--

CREATE SEQUENCE cftf.salt_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cftf.salt_user_id_seq OWNER TO cftf;

--
-- Name: salt_user_id_seq; Type: SEQUENCE OWNED BY; Schema: cftf; Owner: cftf
--

ALTER SEQUENCE cftf.salt_user_id_seq OWNED BY cftf.salt_user.id;


--
-- Name: audit_revision id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_revision ALTER COLUMN id SET DEFAULT nextval('cftf.audit_revision_id_seq'::regclass);


--
-- Name: framework_type id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.framework_type ALTER COLUMN id SET DEFAULT nextval('cftf.framework_type_id_seq'::regclass);


--
-- Name: import_logs id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.import_logs ALTER COLUMN id SET DEFAULT nextval('cftf.import_logs_id_seq'::regclass);


--
-- Name: ls_association id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_association ALTER COLUMN id SET DEFAULT nextval('cftf.ls_association_id_seq'::regclass);


--
-- Name: ls_def_association_grouping id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_association_grouping ALTER COLUMN id SET DEFAULT nextval('cftf.ls_def_association_grouping_id_seq'::regclass);


--
-- Name: ls_def_concept id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_concept ALTER COLUMN id SET DEFAULT nextval('cftf.ls_def_concept_id_seq'::regclass);


--
-- Name: ls_def_grade id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_grade ALTER COLUMN id SET DEFAULT nextval('cftf.ls_def_grade_id_seq'::regclass);


--
-- Name: ls_def_item_type id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_item_type ALTER COLUMN id SET DEFAULT nextval('cftf.ls_def_item_type_id_seq'::regclass);


--
-- Name: ls_def_licence id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_licence ALTER COLUMN id SET DEFAULT nextval('cftf.ls_def_licence_id_seq'::regclass);


--
-- Name: ls_def_subject id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_subject ALTER COLUMN id SET DEFAULT nextval('cftf.ls_def_subject_id_seq'::regclass);


--
-- Name: ls_doc id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc ALTER COLUMN id SET DEFAULT nextval('cftf.ls_doc_id_seq'::regclass);


--
-- Name: ls_item id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_item ALTER COLUMN id SET DEFAULT nextval('cftf.ls_item_id_seq'::regclass);


--
-- Name: mirror_framework id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.mirror_framework ALTER COLUMN id SET DEFAULT nextval('cftf.mirror_framework_id_seq'::regclass);


--
-- Name: mirror_log id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.mirror_log ALTER COLUMN id SET DEFAULT nextval('cftf.mirror_log_id_seq'::regclass);


--
-- Name: mirror_oauth id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.mirror_oauth ALTER COLUMN id SET DEFAULT nextval('cftf.mirror_oauth_id_seq'::regclass);


--
-- Name: mirror_server id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.mirror_server ALTER COLUMN id SET DEFAULT nextval('cftf.mirror_server_id_seq'::regclass);


--
-- Name: rubric id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.rubric ALTER COLUMN id SET DEFAULT nextval('cftf.rubric_id_seq'::regclass);


--
-- Name: rubric_criterion id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.rubric_criterion ALTER COLUMN id SET DEFAULT nextval('cftf.rubric_criterion_id_seq'::regclass);


--
-- Name: rubric_criterion_level id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.rubric_criterion_level ALTER COLUMN id SET DEFAULT nextval('cftf.rubric_criterion_level_id_seq'::regclass);


--
-- Name: salt_additional_field id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_additional_field ALTER COLUMN id SET DEFAULT nextval('cftf.salt_additional_field_id_seq'::regclass);


--
-- Name: salt_association_subtype id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_association_subtype ALTER COLUMN id SET DEFAULT nextval('cftf.salt_association_subtype_id_seq'::regclass);


--
-- Name: salt_change id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_change ALTER COLUMN id SET DEFAULT nextval('cftf.salt_change_id_seq'::regclass);


--
-- Name: salt_comment id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_comment ALTER COLUMN id SET DEFAULT nextval('cftf.salt_comment_id_seq'::regclass);


--
-- Name: salt_comment_upvote id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_comment_upvote ALTER COLUMN id SET DEFAULT nextval('cftf.salt_comment_upvote_id_seq'::regclass);


--
-- Name: salt_object_lock id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_object_lock ALTER COLUMN id SET DEFAULT nextval('cftf.salt_object_lock_id_seq'::regclass);


--
-- Name: salt_org id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_org ALTER COLUMN id SET DEFAULT nextval('cftf.salt_org_id_seq'::regclass);


--
-- Name: salt_user id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_user ALTER COLUMN id SET DEFAULT nextval('cftf.salt_user_id_seq'::regclass);


--
-- Name: salt_user_doc_acl id; Type: DEFAULT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_user_doc_acl ALTER COLUMN id SET DEFAULT nextval('cftf.salt_user_doc_acl_id_seq'::regclass);


--
-- 5. Enter the minimal data set available at the end of execution of opensalt/local-dev/initial_dev_install.sh
--

--
-- Data for Name: audit_ls_association; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_ls_association (id, rev, ls_doc_id, assoc_group_id, origin_lsdoc_id, origin_lsitem_id, destination_lsdoc_id, destination_lsitem_id, identifier, uri, extra, updated_at, changed_at, ls_doc_identifier, ls_doc_uri, origin_node_identifier, origin_node_uri, destination_node_identifier, destination_node_uri, type, seq, revtype, subtype, annotation) FROM stdin;
\.


--
-- Data for Name: audit_ls_def_association_grouping; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_ls_def_association_grouping (id, rev, ls_doc_id, identifier, uri, extra, updated_at, changed_at, title, description, revtype) FROM stdin;
\.


--
-- Data for Name: audit_ls_def_concept; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_ls_def_concept (id, rev, identifier, uri, extra, updated_at, changed_at, title, description, hierarchy_code, keywords, revtype) FROM stdin;
\.


--
-- Data for Name: audit_ls_def_grade; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_ls_def_grade (id, rev, identifier, uri, extra, updated_at, changed_at, title, description, code, rank, revtype) FROM stdin;
1	1	4d2c5cb4-1488-448f-9462-696b53ce9229	level:4d2c5cb4-1488-448f-9462-696b53ce9229	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Infant/toddler	Infant/toddler	IT	1	INS
2	1	f9dbfa6c-f0ed-4134-bb40-e64ac850c7ca	level:f9dbfa6c-f0ed-4134-bb40-e64ac850c7ca	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Preschool	Preschool	PR	2	INS
3	1	48c819af-862c-4cdb-ba0f-558a60548443	level:48c819af-862c-4cdb-ba0f-558a60548443	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Prekindergarten	Prekindergarten	PK	3	INS
4	1	a0c765e1-c750-40bd-8aa8-7e791b97cda5	level:a0c765e1-c750-40bd-8aa8-7e791b97cda5	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Transitional Kindergarten	Transitional Kindergarten	TK	4	INS
5	1	26cda924-a4e7-4b6e-a586-908b1b1222e2	level:26cda924-a4e7-4b6e-a586-908b1b1222e2	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Kindergarten	Kindergarten	KG	5	INS
6	1	14c6e4fe-bed1-4f99-85cf-738f5ce64ddb	level:14c6e4fe-bed1-4f99-85cf-738f5ce64ddb	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	First grade	First grade	01	6	INS
7	1	0d2642e9-d7e8-4903-91cd-4943ac32ca2a	level:0d2642e9-d7e8-4903-91cd-4943ac32ca2a	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Second grade	Second grade	02	7	INS
8	1	0b59cd25-e0b2-4e38-b856-c6d8cfc7dc1f	level:0b59cd25-e0b2-4e38-b856-c6d8cfc7dc1f	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Third grade	Third grade	03	8	INS
9	1	07d57c34-5fb6-4e82-8383-af5fb63f65ab	level:07d57c34-5fb6-4e82-8383-af5fb63f65ab	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Fourth grade	Fourth grade	04	9	INS
10	1	69f64667-b903-452d-9c91-0351b83455c2	level:69f64667-b903-452d-9c91-0351b83455c2	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Fifth grade	Fifth grade	05	10	INS
11	1	f4a33f16-ec2f-468b-955a-5cdf97a252b5	level:f4a33f16-ec2f-468b-955a-5cdf97a252b5	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Sixth grade	Sixth grade	06	11	INS
12	1	2fc9c618-604d-4f90-aa98-1442b0edb6d8	level:2fc9c618-604d-4f90-aa98-1442b0edb6d8	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Seventh grade	Seventh grade	07	12	INS
13	1	4b495023-110e-48d3-9892-1b7d18b74b2b	level:4b495023-110e-48d3-9892-1b7d18b74b2b	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Eighth grade	Eighth grade	08	13	INS
14	1	335cfb2b-0208-4880-8c4e-e74cf4052adf	level:335cfb2b-0208-4880-8c4e-e74cf4052adf	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Ninth grade	Ninth grade	09	14	INS
15	1	557b3a0b-56a0-461a-a1d7-bf47198de45d	level:557b3a0b-56a0-461a-a1d7-bf47198de45d	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Tenth grade	Tenth grade	10	15	INS
16	1	c74474d2-1828-4341-96da-9b8784999016	level:c74474d2-1828-4341-96da-9b8784999016	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Eleventh grade	Eleventh grade	11	16	INS
17	1	14a857f6-c5b8-4a2e-a69f-b4354a58a772	level:14a857f6-c5b8-4a2e-a69f-b4354a58a772	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Twelfth grade	Twelfth grade	12	17	INS
18	1	54a0c7e4-75b8-49ee-b00a-dac069ab6090	level:54a0c7e4-75b8-49ee-b00a-dac069ab6090	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Grade 13	Grade 13	13	18	INS
19	1	8a9f08a6-b2de-41ee-ac06-199c6ad676e0	level:8a9f08a6-b2de-41ee-ac06-199c6ad676e0	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Associate's degree	Associate's degree	AS	19	INS
20	1	6f6bd515-877f-4bd6-8073-dad3c0f93f89	level:6f6bd515-877f-4bd6-8073-dad3c0f93f89	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Bachelor's degree	Bachelor's degree	BA	20	INS
21	1	52361659-924d-4544-ab17-962f57b16581	level:52361659-924d-4544-ab17-962f57b16581	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Post-baccalaureate certificate	Post-baccalaureate certificate	PB	21	INS
22	1	5251cb06-d116-4a73-a893-edddc09d6ed7	level:5251cb06-d116-4a73-a893-edddc09d6ed7	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Master's degree	Master's degree	MD	22	INS
23	1	5b4df0fc-812d-4e46-965b-1c93478be35f	level:5b4df0fc-812d-4e46-965b-1c93478be35f	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Post-master's certificate	Post-master's certificate	PM	23	INS
24	1	18f65aad-b29a-40a8-81d7-137218428f11	level:18f65aad-b29a-40a8-81d7-137218428f11	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Doctoral degree	Doctoral degree	DO	24	INS
25	1	c15af13b-fa91-47dc-8605-4ffd20ace2eb	level:c15af13b-fa91-47dc-8605-4ffd20ace2eb	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Post-doctoral certificate	Post-doctoral certificate	PD	25	INS
26	1	ab25c6a0-a378-492e-9d3c-c57b511c7b80	level:ab25c6a0-a378-492e-9d3c-c57b511c7b80	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Adult Education	Adult Education	AE	26	INS
27	1	d1776264-29f6-448a-8ef1-1d09c86d57f0	level:d1776264-29f6-448a-8ef1-1d09c86d57f0	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Professional or technical credential	Professional or technical credential	PT	27	INS
28	1	8ba30758-3a68-4067-82c0-12cf256f07fe	level:8ba30758-3a68-4067-82c0-12cf256f07fe	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Other	Other	OT	28	INS
\.


--
-- Data for Name: audit_ls_def_item_type; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_ls_def_item_type (id, rev, identifier, uri, extra, updated_at, changed_at, title, description, code, hierarchy_code, revtype) FROM stdin;
\.


--
-- Data for Name: audit_ls_def_licence; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_ls_def_licence (id, rev, identifier, uri, extra, updated_at, changed_at, title, description, licence_text, revtype) FROM stdin;
\.


--
-- Data for Name: audit_ls_def_subject; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_ls_def_subject (id, rev, identifier, uri, extra, updated_at, changed_at, title, description, hierarchy_code, revtype) FROM stdin;
\.


--
-- Data for Name: audit_ls_doc; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_ls_doc (id, rev, org_id, user_id, licence_id, identifier, uri, extra, updated_at, changed_at, official_uri, creator, publisher, title, url_name, version, description, subject, language, adoption_status, status_start, status_end, note, revtype, frameworktype_id, mirrored_framework_id) FROM stdin;
\.


--
-- Data for Name: audit_ls_doc_attribute; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_ls_doc_attribute (attribute, ls_doc_id, rev, value, revtype) FROM stdin;
\.


--
-- Data for Name: audit_ls_item; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_ls_item (id, rev, ls_doc_id, item_type_id, item_type_text, licence_id, identifier, uri, extra, updated_at, ls_doc_identifier, ls_doc_uri, human_coding_scheme, list_enum_in_source, full_statement, abbreviated_statement, concept_keywords, notes, language, educational_alignment, alternative_label, status_start, status_end, changed_at, revtype) FROM stdin;
\.


--
-- Data for Name: audit_revision; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_revision (id, "timestamp", username) FROM stdin;
1	2020-05-25 16:55:25-04	\N
2	2020-05-25 16:55:53-04	
\.


--
-- Data for Name: audit_rubric; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_rubric (id, rev, identifier, uri, extra, updated_at, changed_at, title, description, revtype) FROM stdin;
\.


--
-- Data for Name: audit_rubric_criterion; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_rubric_criterion (id, rev, ls_item_id, rubric_id, identifier, uri, extra, updated_at, changed_at, category, description, weight, "position", revtype) FROM stdin;
\.


--
-- Data for Name: audit_rubric_criterion_level; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_rubric_criterion_level (id, rev, criterion_id, identifier, uri, extra, updated_at, changed_at, description, quality, score, feedback, "position", revtype) FROM stdin;
\.


--
-- Data for Name: audit_salt_change; Type: TABLE DATA; Schema: cftf; Owner: cftf
--
-- 1	2	\N	\N	2020-05-25 16:55:51.055331-04	User "admin" added to "Unknown"	\N	INS

COPY cftf.audit_salt_change (id, rev, user_id, doc_id, changed_at, description, changed, revtype) FROM stdin;
\.


--
-- Data for Name: audit_salt_org; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_salt_org (id, rev, name, revtype) FROM stdin;
1	1	Unknown	INS
\.


--
-- Data for Name: audit_salt_user; Type: TABLE DATA; Schema: cftf; Owner: cftf
--
-- 1	2	1	admin	$argon2id$v=19$m=65536,t=4,p=1$hxpv6tTrpUs0t2DHvKts8w$k2fpdn8HcoIAkY26aSKjWhsPdoygcaSavB6CQSYvAx8	["ROLE_SUPER_USER"]	\N	INS	0

COPY cftf.audit_salt_user (id, rev, org_id, username, password, roles, github_token, revtype, status) FROM stdin;
\.


--
-- Data for Name: audit_salt_user_doc_acl; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.audit_salt_user_doc_acl (id, user_id, doc_id, rev, access, revtype) FROM stdin;
\.


--
-- Data for Name: auth_session; Type: TABLE DATA; Schema: cftf; Owner: cftf
--
-- \\x3836393934396136646564633235626338336336323333613666653266633331	\\x5f7366325f617474726962757465737c613a333a7b733a31383a225f637372662f61757468656e746963617465223b733a34333a223376316d626e583558384d6c7753304f7a45464f343146454d3845355f7667304e4265615345623831574d223b733a32333a225f73656375726974792e6c6173745f757365726e616d65223b733a353a2261646d696e223b733a31343a225f73656375726974795f6d61696e223b733a3233323a224f3a36373a2253796d666f6e795c436f6d706f6e656e745c53656375726974795c47756172645c546f6b656e5c506f737441757468656e7469636174696f6e4775617264546f6b656e223a323a7b693a303b733a343a226d61696e223b693a313b613a353a7b693a303b433a32303a224170705c456e746974795c557365725c55736572223a33303a7b613a323a7b693a303b693a313b693a313b733a353a2261646d696e223b7d7d693a313b623a313b693a323b4e3b693a333b613a303a7b7d693a343b613a313a7b693a303b733a31353a22524f4c455f53555045525f55534552223b7d7d7d223b7d5f7366325f6d6574617c613a333a7b733a313a2275223b693a313539303432353837393b733a313a2263223b693a313539303432353837393b733a313a226c223b733a313a2230223b7d	1590425883	1590512283

COPY cftf.auth_session (id, sess_data, sess_time, sess_lifetime) FROM stdin;
\.


--
-- Data for Name: cache_items; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.cache_items (item_id, item_data, item_lifetime, item_time) FROM stdin;
\.


--
-- Data for Name: framework_type; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.framework_type (id, framework_type) FROM stdin;
\.


--
-- Data for Name: import_logs; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.import_logs (id, ls_doc_id, message_text, message_type, is_read) FROM stdin;
\.


--
-- Data for Name: ls_association; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_association (id, identifier, uri, ls_doc_identifier, ls_doc_uri, ls_doc_id, origin_node_identifier, origin_node_uri, origin_lsdoc_id, origin_lsitem_id, destination_node_identifier, destination_node_uri, destination_lsdoc_id, destination_lsitem_id, type, seq, updated_at, changed_at, assoc_group_id, extra, subtype, annotation) FROM stdin;
\.


--
-- Data for Name: ls_def_association_grouping; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_def_association_grouping (id, identifier, uri, extra, updated_at, changed_at, title, description, ls_doc_id) FROM stdin;
\.


--
-- Data for Name: ls_def_concept; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_def_concept (id, identifier, uri, extra, updated_at, changed_at, title, description, hierarchy_code, keywords) FROM stdin;
\.


--
-- Data for Name: ls_def_grade; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_def_grade (id, identifier, uri, extra, updated_at, changed_at, title, description, code, rank) FROM stdin;
1	4d2c5cb4-1488-448f-9462-696b53ce9229	level:4d2c5cb4-1488-448f-9462-696b53ce9229	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Infant/toddler	Infant/toddler	IT	1
2	f9dbfa6c-f0ed-4134-bb40-e64ac850c7ca	level:f9dbfa6c-f0ed-4134-bb40-e64ac850c7ca	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Preschool	Preschool	PR	2
3	48c819af-862c-4cdb-ba0f-558a60548443	level:48c819af-862c-4cdb-ba0f-558a60548443	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Prekindergarten	Prekindergarten	PK	3
4	a0c765e1-c750-40bd-8aa8-7e791b97cda5	level:a0c765e1-c750-40bd-8aa8-7e791b97cda5	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Transitional Kindergarten	Transitional Kindergarten	TK	4
5	26cda924-a4e7-4b6e-a586-908b1b1222e2	level:26cda924-a4e7-4b6e-a586-908b1b1222e2	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Kindergarten	Kindergarten	KG	5
6	14c6e4fe-bed1-4f99-85cf-738f5ce64ddb	level:14c6e4fe-bed1-4f99-85cf-738f5ce64ddb	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	First grade	First grade	01	6
7	0d2642e9-d7e8-4903-91cd-4943ac32ca2a	level:0d2642e9-d7e8-4903-91cd-4943ac32ca2a	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Second grade	Second grade	02	7
8	0b59cd25-e0b2-4e38-b856-c6d8cfc7dc1f	level:0b59cd25-e0b2-4e38-b856-c6d8cfc7dc1f	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Third grade	Third grade	03	8
9	07d57c34-5fb6-4e82-8383-af5fb63f65ab	level:07d57c34-5fb6-4e82-8383-af5fb63f65ab	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Fourth grade	Fourth grade	04	9
10	69f64667-b903-452d-9c91-0351b83455c2	level:69f64667-b903-452d-9c91-0351b83455c2	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Fifth grade	Fifth grade	05	10
11	f4a33f16-ec2f-468b-955a-5cdf97a252b5	level:f4a33f16-ec2f-468b-955a-5cdf97a252b5	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Sixth grade	Sixth grade	06	11
12	2fc9c618-604d-4f90-aa98-1442b0edb6d8	level:2fc9c618-604d-4f90-aa98-1442b0edb6d8	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Seventh grade	Seventh grade	07	12
13	4b495023-110e-48d3-9892-1b7d18b74b2b	level:4b495023-110e-48d3-9892-1b7d18b74b2b	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Eighth grade	Eighth grade	08	13
14	335cfb2b-0208-4880-8c4e-e74cf4052adf	level:335cfb2b-0208-4880-8c4e-e74cf4052adf	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Ninth grade	Ninth grade	09	14
15	557b3a0b-56a0-461a-a1d7-bf47198de45d	level:557b3a0b-56a0-461a-a1d7-bf47198de45d	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Tenth grade	Tenth grade	10	15
16	c74474d2-1828-4341-96da-9b8784999016	level:c74474d2-1828-4341-96da-9b8784999016	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Eleventh grade	Eleventh grade	11	16
17	14a857f6-c5b8-4a2e-a69f-b4354a58a772	level:14a857f6-c5b8-4a2e-a69f-b4354a58a772	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Twelfth grade	Twelfth grade	12	17
18	54a0c7e4-75b8-49ee-b00a-dac069ab6090	level:54a0c7e4-75b8-49ee-b00a-dac069ab6090	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Grade 13	Grade 13	13	18
19	8a9f08a6-b2de-41ee-ac06-199c6ad676e0	level:8a9f08a6-b2de-41ee-ac06-199c6ad676e0	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Associate's degree	Associate's degree	AS	19
20	6f6bd515-877f-4bd6-8073-dad3c0f93f89	level:6f6bd515-877f-4bd6-8073-dad3c0f93f89	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Bachelor's degree	Bachelor's degree	BA	20
21	52361659-924d-4544-ab17-962f57b16581	level:52361659-924d-4544-ab17-962f57b16581	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Post-baccalaureate certificate	Post-baccalaureate certificate	PB	21
22	5251cb06-d116-4a73-a893-edddc09d6ed7	level:5251cb06-d116-4a73-a893-edddc09d6ed7	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Master's degree	Master's degree	MD	22
23	5b4df0fc-812d-4e46-965b-1c93478be35f	level:5b4df0fc-812d-4e46-965b-1c93478be35f	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Post-master's certificate	Post-master's certificate	PM	23
24	18f65aad-b29a-40a8-81d7-137218428f11	level:18f65aad-b29a-40a8-81d7-137218428f11	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Doctoral degree	Doctoral degree	DO	24
25	c15af13b-fa91-47dc-8605-4ffd20ace2eb	level:c15af13b-fa91-47dc-8605-4ffd20ace2eb	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Post-doctoral certificate	Post-doctoral certificate	PD	25
26	ab25c6a0-a378-492e-9d3c-c57b511c7b80	level:ab25c6a0-a378-492e-9d3c-c57b511c7b80	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Adult Education	Adult Education	AE	26
27	d1776264-29f6-448a-8ef1-1d09c86d57f0	level:d1776264-29f6-448a-8ef1-1d09c86d57f0	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Professional or technical credential	Professional or technical credential	PT	27
28	8ba30758-3a68-4067-82c0-12cf256f07fe	level:8ba30758-3a68-4067-82c0-12cf256f07fe	\N	2020-05-25 16:55:10-04	2020-05-25 16:55:10-04	Other	Other	OT	28
\.


--
-- Data for Name: ls_def_item_type; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_def_item_type (id, identifier, uri, extra, updated_at, changed_at, title, description, code, hierarchy_code) FROM stdin;
\.


--
-- Data for Name: ls_def_licence; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_def_licence (id, identifier, uri, extra, updated_at, changed_at, title, description, licence_text) FROM stdin;
1	7eb5e85a-6fef-59f4-a5f2-6665ab9681db	local:7eb5e85a-6fef-59f4-a5f2-6665ab9681db	\N	2020-05-25 16:55:27-04	2020-05-25 16:55:27-04	Attribution 4.0 International	Creative Commons Attribution 4.0 International	https://creativecommons.org/licenses/by/4.0/legalcode
\.


--
-- Data for Name: ls_def_subject; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_def_subject (id, identifier, uri, extra, updated_at, changed_at, title, description, hierarchy_code) FROM stdin;
\.


--
-- Data for Name: ls_doc; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_doc (id, org_id, user_id, uri, identifier, official_uri, creator, publisher, title, version, description, subject, language, adoption_status, status_start, status_end, note, updated_at, changed_at, url_name, licence_id, extra, frameworktype_id, mirrored_framework_id) FROM stdin;
\.


--
-- Data for Name: ls_doc_attribute; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_doc_attribute (ls_doc_id, attribute, value) FROM stdin;
\.


--
-- Data for Name: ls_doc_subject; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_doc_subject (ls_doc_id, subject_id) FROM stdin;
\.


--
-- Data for Name: ls_item; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_item (id, ls_doc_identifier, ls_doc_id, uri, ls_doc_uri, human_coding_scheme, identifier, list_enum_in_source, full_statement, abbreviated_statement, concept_keywords, notes, language, educational_alignment, type, item_type_id, item_type_text, changed_at, updated_at, extra, licence_id, alternative_label, status_start, status_end) FROM stdin;
\.


--
-- Data for Name: ls_item_concept; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.ls_item_concept (ls_item_id, concept_id) FROM stdin;
\.


--
-- Data for Name: migration_versions; Type: TABLE DATA; Schema: cftf; Owner: cftf
--
--
-- 20160201010100	2020-05-25 16:55:05-04
-- 20160705235303	2020-05-25 16:55:05-04
-- 20160715210821	2020-05-25 16:55:05-04
-- 20160719225459	2020-05-25 16:55:06-04
-- 20160720143502	2020-05-25 16:55:07-04
-- 20160808195223	2020-05-25 16:55:07-04
-- 20160811175559	2020-05-25 16:55:07-04
-- 20160811180746	2020-05-25 16:55:07-04
-- 20160811192520	2020-05-25 16:55:07-04
-- 20160811194318	2020-05-25 16:55:07-04
-- 20160811203854	2020-05-25 16:55:08-04
-- 20160811225500	2020-05-25 16:55:08-04
-- 20160811235255	2020-05-25 16:55:08-04
-- 20160823233434	2020-05-25 16:55:08-04
-- 20160824143305	2020-05-25 16:55:08-04
-- 20160824144330	2020-05-25 16:55:08-04
-- 20160825152738	2020-05-25 16:55:08-04
-- 20160826134721	2020-05-25 16:55:08-04
-- 20160902204929	2020-05-25 16:55:08-04
-- 20160914163710	2020-05-25 16:55:09-04
-- 20160914181326	2020-05-25 16:55:09-04
-- 20160914191941	2020-05-25 16:55:09-04
-- 20160914202820	2020-05-25 16:55:09-04
-- 20160914205741	2020-05-25 16:55:09-04
-- 20160921150507	2020-05-25 16:55:09-04
-- 20160921185958	2020-05-25 16:55:10-04
-- 20160921221450	2020-05-25 16:55:10-04
-- 20160921225507	2020-05-25 16:55:10-04
-- 20160922203010	2020-05-25 16:55:10-04
-- 20160927144534	2020-05-25 16:55:10-04
-- 20160928190320	2020-05-25 16:55:10-04
-- 20160928191216	2020-05-25 16:55:10-04
-- 20161122231452	2020-05-25 16:55:11-04
-- 20161128163007	2020-05-25 16:55:11-04
-- 20161202212214	2020-05-25 16:55:11-04
-- 20161205205017	2020-05-25 16:55:11-04
-- 20161205231342	2020-05-25 16:55:12-04
-- 20161208193908	2020-05-25 16:55:12-04
-- 20170201212306	2020-05-25 16:55:12-04
-- 20170206220209	2020-05-25 16:55:12-04
-- 20170207201734	2020-05-25 16:55:12-04
-- 20170324210751	2020-05-25 16:55:13-04
-- 20170412180227	2020-05-25 16:55:13-04
-- 20170504134137	2020-05-25 16:55:13-04
-- 20170505215412	2020-05-25 16:55:13-04
-- 20170506002933	2020-05-25 16:55:13-04
-- 20170510163859	2020-05-25 16:55:13-04
-- 20170511154711	2020-05-25 16:55:14-04
-- 20170512001154	2020-05-25 16:55:14-04
-- 20170523161837	2020-05-25 16:55:14-04
-- 20170607010127	2020-05-25 16:55:16-04
-- 20170823192751	2020-05-25 16:55:16-04
-- 20170911215839	2020-05-25 16:55:16-04
-- 20170925222726	2020-05-25 16:55:16-04
-- 20171003210312	2020-05-25 16:55:16-04
-- 20171004005547	2020-05-25 16:55:21-04
-- 20171102221106	2020-05-25 16:55:21-04
-- 20171204152157	2020-05-25 16:55:21-04
-- 20171214142307	2020-05-25 16:55:22-04
-- 20171227235729	2020-05-25 16:55:25-04
-- 20180103230827	2020-05-25 16:55:25-04
-- 20180108162841	2020-05-25 16:55:25-04
-- 20180109185357	2020-05-25 16:55:25-04
-- 20180115230815	2020-05-25 16:55:26-04
-- 20180117204204	2020-05-25 16:55:26-04
-- 20180130115104	2020-05-25 16:55:26-04
-- 20180130193130	2020-05-25 16:55:27-04
-- 20180201165909	2020-05-25 16:55:27-04
-- 20180219235129	2020-05-25 16:55:27-04
-- 20180619023134	2020-05-25 16:55:27-04
-- 20180718181228	2020-05-25 16:55:27-04
-- 20180910183057	2020-05-25 16:55:27-04
-- 20181210195400	2020-05-25 16:55:27-04
-- 20190416151741	2020-05-25 16:55:28-04
-- 20190530232540	2020-05-25 16:55:28-04
-- 20191002135839	2020-05-25 16:55:28-04
-- 20191002185729	2020-05-25 16:55:28-04
-- 20191003181501	2020-05-25 16:55:28-04
-- 20191004200339	2020-05-25 16:55:29-04
-- 20191007212215	2020-05-25 16:55:29-04
-- 20191008182209	2020-05-25 16:55:29-04
-- 20191015224044	2020-05-25 16:55:29-04
-- 20191016232759	2020-05-25 16:55:32-04
-- 20191105230939	2020-05-25 16:55:33-04
-- 20191106094004	2020-05-25 16:55:34-04
-- 20191106213213	2020-05-25 16:55:34-04
-- 20191108173538	2020-05-25 16:55:35-04
-- 20191210194132	2020-05-25 16:55:35-04
-- 20191210200643	2020-05-25 16:55:35-04
-- 20191210204257	2020-05-25 16:55:35-04
-- 20191213225240	2020-05-25 16:55:36-04
-- 20200415170428	2020-05-25 16:55:36-04
-- 20200415182443	2020-05-25 16:55:36-04

COPY cftf.migration_versions (version, executed_at) FROM stdin;
\.


--
-- Data for Name: mirror_framework; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.mirror_framework (id, server_id, url, identifier, creator, title, include, priority, status, status_count, last_check, last_success, last_failure, last_change, next_check, error_type, updated_at, last_content, last_success_content) FROM stdin;
\.


--
-- Data for Name: mirror_log; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.mirror_log (id, mirror_id, status, message, occurred_at) FROM stdin;
\.


--
-- Data for Name: mirror_oauth; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.mirror_oauth (id, endpoint, auth_key, auth_secret, updated_at) FROM stdin;
\.


--
-- Data for Name: mirror_server; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.mirror_server (id, credentials_id, url, api_type, check_server, add_found, priority, next_check, last_check, updated_at) FROM stdin;
\.


--
-- Data for Name: rubric; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.rubric (id, identifier, uri, extra, updated_at, changed_at, title, description) FROM stdin;
\.


--
-- Data for Name: rubric_criterion; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.rubric_criterion (id, ls_item_id, rubric_id, identifier, uri, extra, updated_at, changed_at, category, description, weight, "position") FROM stdin;
\.


--
-- Data for Name: rubric_criterion_level; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.rubric_criterion_level (id, criterion_id, identifier, uri, extra, updated_at, changed_at, description, quality, score, feedback, "position") FROM stdin;
\.


--
-- Data for Name: salt_additional_field; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.salt_additional_field (id, name, applies_to, display_name, type, type_info) FROM stdin;
\.


--
-- Data for Name: salt_association_subtype; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.salt_association_subtype (id, name, parent_type, direction, description) FROM stdin;
\.


--
-- Data for Name: salt_change; Type: TABLE DATA; Schema: cftf; Owner: cftf
--
-- 1	\N	\N	2020-05-25 16:55:51.055331-04	User "admin" added to "Unknown"	\N


COPY cftf.salt_change (id, user_id, doc_id, changed_at, description, changed) FROM stdin;
\.


--
-- Data for Name: salt_comment; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.salt_comment (id, parent_id, user_id, content, created_at, updated_at, document_id, item_id, file_url, file_mime_type) FROM stdin;
\.


--
-- Data for Name: salt_comment_upvote; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.salt_comment_upvote (id, comment_id, user_id, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: salt_object_lock; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.salt_object_lock (id, user_id, doc_id, expiry, obj_type, obj_id) FROM stdin;
\.


--
-- Data for Name: salt_org; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.salt_org (id, name) FROM stdin;
1	Unknown
\.


--
-- Data for Name: salt_user; Type: TABLE DATA; Schema: cftf; Owner: cftf
--
-- 1	1	admin	$argon2id$v=19$m=65536,t=4,p=1$hxpv6tTrpUs0t2DHvKts8w$k2fpdn8HcoIAkY26aSKjWhsPdoygcaSavB6CQSYvAx8	["ROLE_SUPER_USER"]	\N	0

COPY cftf.salt_user (id, org_id, username, password, roles, github_token, status) FROM stdin;
\.


--
-- Data for Name: salt_user_doc_acl; Type: TABLE DATA; Schema: cftf; Owner: cftf
--

COPY cftf.salt_user_doc_acl (id, user_id, doc_id, access) FROM stdin;
\.


--
-- Name: audit_revision_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.audit_revision_id_seq', 2, true);


--
-- Name: framework_type_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.framework_type_id_seq', 1, true);


--
-- Name: import_logs_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.import_logs_id_seq', 1, true);


--
-- Name: ls_association_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.ls_association_id_seq', 1, true);


--
-- Name: ls_def_association_grouping_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.ls_def_association_grouping_id_seq', 1, true);


--
-- Name: ls_def_concept_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.ls_def_concept_id_seq', 1, true);


--
-- Name: ls_def_grade_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.ls_def_grade_id_seq', 28, true);


--
-- Name: ls_def_item_type_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.ls_def_item_type_id_seq', 1, true);


--
-- Name: ls_def_licence_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.ls_def_licence_id_seq', 1, true);


--
-- Name: ls_def_subject_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.ls_def_subject_id_seq', 1, true);


--
-- Name: ls_doc_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.ls_doc_id_seq', 1, true);


--
-- Name: ls_item_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.ls_item_id_seq', 1, true);


--
-- Name: mirror_framework_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.mirror_framework_id_seq', 1, true);


--
-- Name: mirror_log_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.mirror_log_id_seq', 1, true);


--
-- Name: mirror_oauth_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.mirror_oauth_id_seq', 1, true);


--
-- Name: mirror_server_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.mirror_server_id_seq', 1, true);


--
-- Name: rubric_criterion_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.rubric_criterion_id_seq', 1, true);


--
-- Name: rubric_criterion_level_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.rubric_criterion_level_id_seq', 1, true);


--
-- Name: rubric_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.rubric_id_seq', 1, true);


--
-- Name: salt_additional_field_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.salt_additional_field_id_seq', 1, true);


--
-- Name: salt_association_subtype_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.salt_association_subtype_id_seq', 1, true);


--
-- Name: salt_change_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.salt_change_id_seq', 1, true);


--
-- Name: salt_comment_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.salt_comment_id_seq', 1, true);


--
-- Name: salt_comment_upvote_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.salt_comment_upvote_id_seq', 1, true);


--
-- Name: salt_object_lock_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.salt_object_lock_id_seq', 1, true);


--
-- Name: salt_org_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.salt_org_id_seq', 1, true);


--
-- Name: salt_user_doc_acl_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.salt_user_doc_acl_id_seq', 1, true);


--
-- Name: salt_user_id_seq; Type: SEQUENCE SET; Schema: cftf; Owner: cftf
--

SELECT pg_catalog.setval('cftf.salt_user_id_seq', 1, true);


--
-- Name: audit_ls_association idx_18686_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_ls_association
    ADD CONSTRAINT idx_18686_primary PRIMARY KEY (id, rev);


--
-- Name: audit_ls_def_association_grouping idx_18692_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_ls_def_association_grouping
    ADD CONSTRAINT idx_18692_primary PRIMARY KEY (id, rev);


--
-- Name: audit_ls_def_concept idx_18698_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_ls_def_concept
    ADD CONSTRAINT idx_18698_primary PRIMARY KEY (id, rev);


--
-- Name: audit_ls_def_grade idx_18704_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_ls_def_grade
    ADD CONSTRAINT idx_18704_primary PRIMARY KEY (id, rev);


--
-- Name: audit_ls_def_item_type idx_18710_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_ls_def_item_type
    ADD CONSTRAINT idx_18710_primary PRIMARY KEY (id, rev);


--
-- Name: audit_ls_def_licence idx_18716_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_ls_def_licence
    ADD CONSTRAINT idx_18716_primary PRIMARY KEY (id, rev);


--
-- Name: audit_ls_def_subject idx_18722_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_ls_def_subject
    ADD CONSTRAINT idx_18722_primary PRIMARY KEY (id, rev);


--
-- Name: audit_ls_doc idx_18728_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_ls_doc
    ADD CONSTRAINT idx_18728_primary PRIMARY KEY (id, rev);


--
-- Name: audit_ls_doc_attribute idx_18734_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_ls_doc_attribute
    ADD CONSTRAINT idx_18734_primary PRIMARY KEY (ls_doc_id, attribute, rev);


--
-- Name: audit_ls_item idx_18740_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_ls_item
    ADD CONSTRAINT idx_18740_primary PRIMARY KEY (id, rev);


--
-- Name: audit_revision idx_18748_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_revision
    ADD CONSTRAINT idx_18748_primary PRIMARY KEY (id);


--
-- Name: audit_rubric idx_18752_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_rubric
    ADD CONSTRAINT idx_18752_primary PRIMARY KEY (id, rev);


--
-- Name: audit_rubric_criterion idx_18758_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_rubric_criterion
    ADD CONSTRAINT idx_18758_primary PRIMARY KEY (id, rev);


--
-- Name: audit_rubric_criterion_level idx_18764_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_rubric_criterion_level
    ADD CONSTRAINT idx_18764_primary PRIMARY KEY (id, rev);


--
-- Name: audit_salt_change idx_18770_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_salt_change
    ADD CONSTRAINT idx_18770_primary PRIMARY KEY (id, rev);


--
-- Name: audit_salt_org idx_18776_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_salt_org
    ADD CONSTRAINT idx_18776_primary PRIMARY KEY (id, rev);


--
-- Name: audit_salt_user idx_18779_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_salt_user
    ADD CONSTRAINT idx_18779_primary PRIMARY KEY (id, rev);


--
-- Name: audit_salt_user_doc_acl idx_18786_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.audit_salt_user_doc_acl
    ADD CONSTRAINT idx_18786_primary PRIMARY KEY (id, rev);


--
-- Name: auth_session idx_18789_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.auth_session
    ADD CONSTRAINT idx_18789_primary PRIMARY KEY (id);


--
-- Name: cache_items idx_18795_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.cache_items
    ADD CONSTRAINT idx_18795_primary PRIMARY KEY (item_id);


--
-- Name: framework_type idx_18803_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.framework_type
    ADD CONSTRAINT idx_18803_primary PRIMARY KEY (id);


--
-- Name: import_logs idx_18809_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.import_logs
    ADD CONSTRAINT idx_18809_primary PRIMARY KEY (id);


--
-- Name: ls_association idx_18816_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_association
    ADD CONSTRAINT idx_18816_primary PRIMARY KEY (id);


--
-- Name: ls_def_association_grouping idx_18825_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_association_grouping
    ADD CONSTRAINT idx_18825_primary PRIMARY KEY (id);


--
-- Name: ls_def_concept idx_18834_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_concept
    ADD CONSTRAINT idx_18834_primary PRIMARY KEY (id);


--
-- Name: ls_def_grade idx_18843_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_grade
    ADD CONSTRAINT idx_18843_primary PRIMARY KEY (id);


--
-- Name: ls_def_item_type idx_18852_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_item_type
    ADD CONSTRAINT idx_18852_primary PRIMARY KEY (id);


--
-- Name: ls_def_licence idx_18861_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_licence
    ADD CONSTRAINT idx_18861_primary PRIMARY KEY (id);


--
-- Name: ls_def_subject idx_18870_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_subject
    ADD CONSTRAINT idx_18870_primary PRIMARY KEY (id);


--
-- Name: ls_doc idx_18879_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc
    ADD CONSTRAINT idx_18879_primary PRIMARY KEY (id);


--
-- Name: ls_doc_attribute idx_18886_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc_attribute
    ADD CONSTRAINT idx_18886_primary PRIMARY KEY (ls_doc_id, attribute);


--
-- Name: ls_doc_subject idx_18892_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc_subject
    ADD CONSTRAINT idx_18892_primary PRIMARY KEY (ls_doc_id, subject_id);


--
-- Name: ls_item idx_18897_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_item
    ADD CONSTRAINT idx_18897_primary PRIMARY KEY (id);


--
-- Name: ls_item_concept idx_18904_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_item_concept
    ADD CONSTRAINT idx_18904_primary PRIMARY KEY (ls_item_id, concept_id);


--
-- Name: migration_versions idx_18907_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.migration_versions
    ADD CONSTRAINT idx_18907_primary PRIMARY KEY (version);


--
-- Name: mirror_framework idx_18912_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.mirror_framework
    ADD CONSTRAINT idx_18912_primary PRIMARY KEY (id);


--
-- Name: mirror_log idx_18924_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.mirror_log
    ADD CONSTRAINT idx_18924_primary PRIMARY KEY (id);


--
-- Name: mirror_oauth idx_18933_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.mirror_oauth
    ADD CONSTRAINT idx_18933_primary PRIMARY KEY (id);


--
-- Name: mirror_server idx_18942_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.mirror_server
    ADD CONSTRAINT idx_18942_primary PRIMARY KEY (id);


--
-- Name: rubric idx_18952_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.rubric
    ADD CONSTRAINT idx_18952_primary PRIMARY KEY (id);


--
-- Name: rubric_criterion idx_18961_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.rubric_criterion
    ADD CONSTRAINT idx_18961_primary PRIMARY KEY (id);


--
-- Name: rubric_criterion_level idx_18970_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.rubric_criterion_level
    ADD CONSTRAINT idx_18970_primary PRIMARY KEY (id);


--
-- Name: salt_additional_field idx_18979_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_additional_field
    ADD CONSTRAINT idx_18979_primary PRIMARY KEY (id);


--
-- Name: salt_association_subtype idx_18988_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_association_subtype
    ADD CONSTRAINT idx_18988_primary PRIMARY KEY (id);


--
-- Name: salt_change idx_18997_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_change
    ADD CONSTRAINT idx_18997_primary PRIMARY KEY (id);


--
-- Name: salt_comment idx_19006_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_comment
    ADD CONSTRAINT idx_19006_primary PRIMARY KEY (id);


--
-- Name: salt_comment_upvote idx_19015_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_comment_upvote
    ADD CONSTRAINT idx_19015_primary PRIMARY KEY (id);


--
-- Name: salt_object_lock idx_19021_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_object_lock
    ADD CONSTRAINT idx_19021_primary PRIMARY KEY (id);


--
-- Name: salt_org idx_19030_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_org
    ADD CONSTRAINT idx_19030_primary PRIMARY KEY (id);


--
-- Name: salt_user idx_19036_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_user
    ADD CONSTRAINT idx_19036_primary PRIMARY KEY (id);


--
-- Name: salt_user_doc_acl idx_19046_primary; Type: CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_user_doc_acl
    ADD CONSTRAINT idx_19046_primary PRIMARY KEY (id);


--
-- Name: idx_18686_rev_ab9f033153ddaddc13326ef55b668486_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18686_rev_ab9f033153ddaddc13326ef55b668486_idx ON cftf.audit_ls_association USING btree (rev);


--
-- Name: idx_18692_rev_bf7194a1c00561d84a1d7e91cb7c75be_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18692_rev_bf7194a1c00561d84a1d7e91cb7c75be_idx ON cftf.audit_ls_def_association_grouping USING btree (rev);


--
-- Name: idx_18698_rev_75324ebee3373577889b17bc22abf34e_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18698_rev_75324ebee3373577889b17bc22abf34e_idx ON cftf.audit_ls_def_concept USING btree (rev);


--
-- Name: idx_18704_rev_45e38822f0d366b3b685da14d9e5debb_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18704_rev_45e38822f0d366b3b685da14d9e5debb_idx ON cftf.audit_ls_def_grade USING btree (rev);


--
-- Name: idx_18710_rev_2d6815a36298d9fed8cbaa375f32e90d_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18710_rev_2d6815a36298d9fed8cbaa375f32e90d_idx ON cftf.audit_ls_def_item_type USING btree (rev);


--
-- Name: idx_18716_rev_065fb16d0e1a3cb4b15539c2daa33f05_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18716_rev_065fb16d0e1a3cb4b15539c2daa33f05_idx ON cftf.audit_ls_def_licence USING btree (rev);


--
-- Name: idx_18722_rev_9aeadfa556e645f082aebe4697c43d9e_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18722_rev_9aeadfa556e645f082aebe4697c43d9e_idx ON cftf.audit_ls_def_subject USING btree (rev);


--
-- Name: idx_18728_rev_2017c4975e95098d54218556d75e37b6_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18728_rev_2017c4975e95098d54218556d75e37b6_idx ON cftf.audit_ls_doc USING btree (rev);


--
-- Name: idx_18734_rev_85ec0ebcb7db34facfef5f4dae36f48b_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18734_rev_85ec0ebcb7db34facfef5f4dae36f48b_idx ON cftf.audit_ls_doc_attribute USING btree (rev);


--
-- Name: idx_18740_rev_6d8f50f455093038b4d1fe3c1726bbd2_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18740_rev_6d8f50f455093038b4d1fe3c1726bbd2_idx ON cftf.audit_ls_item USING btree (rev);


--
-- Name: idx_18752_rev_5fd145a56a99316eb4b8a09af1f272dd_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18752_rev_5fd145a56a99316eb4b8a09af1f272dd_idx ON cftf.audit_rubric USING btree (rev);


--
-- Name: idx_18758_rev_c95d29726857963f63405978aa1e6853_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18758_rev_c95d29726857963f63405978aa1e6853_idx ON cftf.audit_rubric_criterion USING btree (rev);


--
-- Name: idx_18764_rev_5a28dfe017e53a59266a717428a3d7a8_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18764_rev_5a28dfe017e53a59266a717428a3d7a8_idx ON cftf.audit_rubric_criterion_level USING btree (rev);


--
-- Name: idx_18770_changed_doc; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18770_changed_doc ON cftf.audit_salt_change USING btree (doc_id, changed_at);


--
-- Name: idx_18770_rev_f4da141f313c5617c27c4f1fb9f2a4a1_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18770_rev_f4da141f313c5617c27c4f1fb9f2a4a1_idx ON cftf.audit_salt_change USING btree (rev);


--
-- Name: idx_18776_rev_59b3da2b9e2bcba5d4a77563efc38235_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18776_rev_59b3da2b9e2bcba5d4a77563efc38235_idx ON cftf.audit_salt_org USING btree (rev);


--
-- Name: idx_18779_rev_46f55645cc7f32a05776ae6c103d5adb_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18779_rev_46f55645cc7f32a05776ae6c103d5adb_idx ON cftf.audit_salt_user USING btree (rev);


--
-- Name: idx_18786_rev_8112d0e5935d0790cc5f0299a2408621_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18786_rev_8112d0e5935d0790cc5f0299a2408621_idx ON cftf.audit_salt_user_doc_acl USING btree (rev);


--
-- Name: idx_18809_idx_1da328dc9388802c; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18809_idx_1da328dc9388802c ON cftf.import_logs USING btree (ls_doc_id);


--
-- Name: idx_18816_dest_id_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18816_dest_id_idx ON cftf.ls_association USING btree (destination_node_identifier);


--
-- Name: idx_18816_idx_a84022d434c423c4; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18816_idx_a84022d434c423c4 ON cftf.ls_association USING btree (origin_lsdoc_id);


--
-- Name: idx_18816_idx_a84022d44c0c393b; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18816_idx_a84022d44c0c393b ON cftf.ls_association USING btree (origin_lsitem_id);


--
-- Name: idx_18816_idx_a84022d459c28905; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18816_idx_a84022d459c28905 ON cftf.ls_association USING btree (destination_lsdoc_id);


--
-- Name: idx_18816_idx_a84022d45be201d2; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18816_idx_a84022d45be201d2 ON cftf.ls_association USING btree (assoc_group_id);


--
-- Name: idx_18816_idx_a84022d49388802c; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18816_idx_a84022d49388802c ON cftf.ls_association USING btree (ls_doc_id);


--
-- Name: idx_18816_idx_a84022d4a002cdb7; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18816_idx_a84022d4a002cdb7 ON cftf.ls_association USING btree (destination_lsitem_id);


--
-- Name: idx_18816_orig_id_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18816_orig_id_idx ON cftf.ls_association USING btree (origin_node_identifier);


--
-- Name: idx_18816_uniq_a84022d4772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18816_uniq_a84022d4772e836a ON cftf.ls_association USING btree (identifier);


--
-- Name: idx_18816_uniq_a84022d4841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18816_uniq_a84022d4841cb121 ON cftf.ls_association USING btree (uri);


--
-- Name: idx_18825_idx_6a465b629388802c; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18825_idx_6a465b629388802c ON cftf.ls_def_association_grouping USING btree (ls_doc_id);


--
-- Name: idx_18825_uniq_6a465b62772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18825_uniq_6a465b62772e836a ON cftf.ls_def_association_grouping USING btree (identifier);


--
-- Name: idx_18825_uniq_6a465b62841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18825_uniq_6a465b62841cb121 ON cftf.ls_def_association_grouping USING btree (uri);


--
-- Name: idx_18834_uniq_30d83e10772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18834_uniq_30d83e10772e836a ON cftf.ls_def_concept USING btree (identifier);


--
-- Name: idx_18834_uniq_30d83e10841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18834_uniq_30d83e10841cb121 ON cftf.ls_def_concept USING btree (uri);


--
-- Name: idx_18843_uniq_6a10ea72772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18843_uniq_6a10ea72772e836a ON cftf.ls_def_grade USING btree (identifier);


--
-- Name: idx_18843_uniq_6a10ea72841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18843_uniq_6a10ea72841cb121 ON cftf.ls_def_grade USING btree (uri);


--
-- Name: idx_18852_uniq_3844f7b6772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18852_uniq_3844f7b6772e836a ON cftf.ls_def_item_type USING btree (identifier);


--
-- Name: idx_18852_uniq_3844f7b6841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18852_uniq_3844f7b6841cb121 ON cftf.ls_def_item_type USING btree (uri);


--
-- Name: idx_18861_uniq_ca38b808772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18861_uniq_ca38b808772e836a ON cftf.ls_def_licence USING btree (identifier);


--
-- Name: idx_18861_uniq_ca38b808841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18861_uniq_ca38b808841cb121 ON cftf.ls_def_licence USING btree (uri);


--
-- Name: idx_18870_uniq_2c5c603a772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18870_uniq_2c5c603a772e836a ON cftf.ls_def_subject USING btree (identifier);


--
-- Name: idx_18870_uniq_2c5c603a841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18870_uniq_2c5c603a841cb121 ON cftf.ls_def_subject USING btree (uri);


--
-- Name: idx_18879_idx_9ae8cf1f26ef07c9; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18879_idx_9ae8cf1f26ef07c9 ON cftf.ls_doc USING btree (licence_id);


--
-- Name: idx_18879_idx_9ae8cf1f3c4c17b2; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18879_idx_9ae8cf1f3c4c17b2 ON cftf.ls_doc USING btree (frameworktype_id);


--
-- Name: idx_18879_idx_9ae8cf1fa76ed395; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18879_idx_9ae8cf1fa76ed395 ON cftf.ls_doc USING btree (user_id);


--
-- Name: idx_18879_idx_9ae8cf1ff4837c1b; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18879_idx_9ae8cf1ff4837c1b ON cftf.ls_doc USING btree (org_id);


--
-- Name: idx_18879_uniq_9ae8cf1f4077b7be; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18879_uniq_9ae8cf1f4077b7be ON cftf.ls_doc USING btree (url_name);


--
-- Name: idx_18879_uniq_9ae8cf1f772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18879_uniq_9ae8cf1f772e836a ON cftf.ls_doc USING btree (identifier);


--
-- Name: idx_18879_uniq_9ae8cf1f841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18879_uniq_9ae8cf1f841cb121 ON cftf.ls_doc USING btree (uri);


--
-- Name: idx_18879_uniq_9ae8cf1ff66a84b6; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18879_uniq_9ae8cf1ff66a84b6 ON cftf.ls_doc USING btree (mirrored_framework_id);


--
-- Name: idx_18886_idx_1db04fbc9388802c; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18886_idx_1db04fbc9388802c ON cftf.ls_doc_attribute USING btree (ls_doc_id);


--
-- Name: idx_18892_idx_71e9b5bc23edc87; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18892_idx_71e9b5bc23edc87 ON cftf.ls_doc_subject USING btree (subject_id);


--
-- Name: idx_18892_idx_71e9b5bc9388802c; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18892_idx_71e9b5bc9388802c ON cftf.ls_doc_subject USING btree (ls_doc_id);


--
-- Name: idx_18897_idx_d8d0249826ef07c9; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18897_idx_d8d0249826ef07c9 ON cftf.ls_item USING btree (licence_id);


--
-- Name: idx_18897_idx_d8d024989388802c; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18897_idx_d8d024989388802c ON cftf.ls_item USING btree (ls_doc_id);


--
-- Name: idx_18897_idx_d8d02498ce11aac7; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18897_idx_d8d02498ce11aac7 ON cftf.ls_item USING btree (item_type_id);


--
-- Name: idx_18897_uniq_d8d02498772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18897_uniq_d8d02498772e836a ON cftf.ls_item USING btree (identifier);


--
-- Name: idx_18897_uniq_d8d02498841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18897_uniq_d8d02498841cb121 ON cftf.ls_item USING btree (uri);


--
-- Name: idx_18904_idx_ff2e1b51e27a1fd2; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18904_idx_ff2e1b51e27a1fd2 ON cftf.ls_item_concept USING btree (ls_item_id);


--
-- Name: idx_18904_idx_ff2e1b51f909284e; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18904_idx_ff2e1b51f909284e ON cftf.ls_item_concept USING btree (concept_id);


--
-- Name: idx_18912_idx_f6bdd7191844e6b7; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18912_idx_f6bdd7191844e6b7 ON cftf.mirror_framework USING btree (server_id);


--
-- Name: idx_18924_idx_3b7127a5fac830ac; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18924_idx_3b7127a5fac830ac ON cftf.mirror_log USING btree (mirror_id);


--
-- Name: idx_18942_idx_f43f213f41e8b2e5; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18942_idx_f43f213f41e8b2e5 ON cftf.mirror_server USING btree (credentials_id);


--
-- Name: idx_18952_uniq_60c4016c772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18952_uniq_60c4016c772e836a ON cftf.rubric USING btree (identifier);


--
-- Name: idx_18952_uniq_60c4016c841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18952_uniq_60c4016c841cb121 ON cftf.rubric USING btree (uri);


--
-- Name: idx_18961_idx_98e476f9a29ec0fc; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18961_idx_98e476f9a29ec0fc ON cftf.rubric_criterion USING btree (rubric_id);


--
-- Name: idx_18961_idx_98e476f9e27a1fd2; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18961_idx_98e476f9e27a1fd2 ON cftf.rubric_criterion USING btree (ls_item_id);


--
-- Name: idx_18961_uniq_98e476f9772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18961_uniq_98e476f9772e836a ON cftf.rubric_criterion USING btree (identifier);


--
-- Name: idx_18961_uniq_98e476f9841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18961_uniq_98e476f9841cb121 ON cftf.rubric_criterion USING btree (uri);


--
-- Name: idx_18970_idx_fec0421697766307; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18970_idx_fec0421697766307 ON cftf.rubric_criterion_level USING btree (criterion_id);


--
-- Name: idx_18970_uniq_fec04216772e836a; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18970_uniq_fec04216772e836a ON cftf.rubric_criterion_level USING btree (identifier);


--
-- Name: idx_18970_uniq_fec04216841cb121; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18970_uniq_fec04216841cb121 ON cftf.rubric_criterion_level USING btree (uri);


--
-- Name: idx_18979_applies_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18979_applies_idx ON cftf.salt_additional_field USING btree (applies_to);


--
-- Name: idx_18979_uniq_561885835e237e06; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18979_uniq_561885835e237e06 ON cftf.salt_additional_field USING btree (name);


--
-- Name: idx_18988_uniq_b5759a895e237e06; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18988_uniq_b5759a895e237e06 ON cftf.salt_association_subtype USING btree (name);


--
-- Name: idx_18997_change_time_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18997_change_time_idx ON cftf.salt_change USING btree (changed_at);


--
-- Name: idx_18997_idx_8427c157a76ed395; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_18997_idx_8427c157a76ed395 ON cftf.salt_change USING btree (user_id);


--
-- Name: idx_18997_uniq_8427c157895648bc; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_18997_uniq_8427c157895648bc ON cftf.salt_change USING btree (doc_id);


--
-- Name: idx_19006_idx_5ad1c6cc126f525e; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19006_idx_5ad1c6cc126f525e ON cftf.salt_comment USING btree (item_id);


--
-- Name: idx_19006_idx_5ad1c6cc727aca70; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19006_idx_5ad1c6cc727aca70 ON cftf.salt_comment USING btree (parent_id);


--
-- Name: idx_19006_idx_5ad1c6cca76ed395; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19006_idx_5ad1c6cca76ed395 ON cftf.salt_comment USING btree (user_id);


--
-- Name: idx_19006_idx_5ad1c6ccc33f7837; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19006_idx_5ad1c6ccc33f7837 ON cftf.salt_comment USING btree (document_id);


--
-- Name: idx_19015_comment_user; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_19015_comment_user ON cftf.salt_comment_upvote USING btree (comment_id, user_id);


--
-- Name: idx_19015_idx_4db1d19ca76ed395; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19015_idx_4db1d19ca76ed395 ON cftf.salt_comment_upvote USING btree (user_id);


--
-- Name: idx_19015_idx_4db1d19cf8697d13; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19015_idx_4db1d19cf8697d13 ON cftf.salt_comment_upvote USING btree (comment_id);


--
-- Name: idx_19021_expiry_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19021_expiry_idx ON cftf.salt_object_lock USING btree (expiry);


--
-- Name: idx_19021_idx_247092f895648bc; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19021_idx_247092f895648bc ON cftf.salt_object_lock USING btree (doc_id);


--
-- Name: idx_19021_idx_247092fa76ed395; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19021_idx_247092fa76ed395 ON cftf.salt_object_lock USING btree (user_id);


--
-- Name: idx_19021_lock_obj_idx; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_19021_lock_obj_idx ON cftf.salt_object_lock USING btree (obj_type, obj_id);


--
-- Name: idx_19030_uniq_762fb035e237e06; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_19030_uniq_762fb035e237e06 ON cftf.salt_org USING btree (name);


--
-- Name: idx_19036_idx_f9577392f4837c1b; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19036_idx_f9577392f4837c1b ON cftf.salt_user USING btree (org_id);


--
-- Name: idx_19036_uniq_f9577392f85e0677; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_19036_uniq_f9577392f85e0677 ON cftf.salt_user USING btree (username);


--
-- Name: idx_19046_idx_85c83e4a895648bc; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19046_idx_85c83e4a895648bc ON cftf.salt_user_doc_acl USING btree (doc_id);


--
-- Name: idx_19046_idx_85c83e4aa76ed395; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE INDEX idx_19046_idx_85c83e4aa76ed395 ON cftf.salt_user_doc_acl USING btree (user_id);


--
-- Name: idx_19046_uniq_acl_id; Type: INDEX; Schema: cftf; Owner: cftf
--

CREATE UNIQUE INDEX idx_19046_uniq_acl_id ON cftf.salt_user_doc_acl USING btree (doc_id, user_id);


--
-- Name: ls_doc_attribute fk_1db04fbc9388802c; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc_attribute
    ADD CONSTRAINT fk_1db04fbc9388802c FOREIGN KEY (ls_doc_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: salt_object_lock fk_247092f895648bc; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_object_lock
    ADD CONSTRAINT fk_247092f895648bc FOREIGN KEY (doc_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: salt_object_lock fk_247092fa76ed395; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_object_lock
    ADD CONSTRAINT fk_247092fa76ed395 FOREIGN KEY (user_id) REFERENCES cftf.salt_user(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: mirror_log fk_3b7127a5fac830ac; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.mirror_log
    ADD CONSTRAINT fk_3b7127a5fac830ac FOREIGN KEY (mirror_id) REFERENCES cftf.mirror_framework(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: salt_comment_upvote fk_4db1d19ca76ed395; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_comment_upvote
    ADD CONSTRAINT fk_4db1d19ca76ed395 FOREIGN KEY (user_id) REFERENCES cftf.salt_user(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: salt_comment_upvote fk_4db1d19cf8697d13; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_comment_upvote
    ADD CONSTRAINT fk_4db1d19cf8697d13 FOREIGN KEY (comment_id) REFERENCES cftf.salt_comment(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: salt_comment fk_5ad1c6cc126f525e; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_comment
    ADD CONSTRAINT fk_5ad1c6cc126f525e FOREIGN KEY (item_id) REFERENCES cftf.ls_item(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: salt_comment fk_5ad1c6cc727aca70; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_comment
    ADD CONSTRAINT fk_5ad1c6cc727aca70 FOREIGN KEY (parent_id) REFERENCES cftf.salt_comment(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: salt_comment fk_5ad1c6cca76ed395; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_comment
    ADD CONSTRAINT fk_5ad1c6cca76ed395 FOREIGN KEY (user_id) REFERENCES cftf.salt_user(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: salt_comment fk_5ad1c6ccc33f7837; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_comment
    ADD CONSTRAINT fk_5ad1c6ccc33f7837 FOREIGN KEY (document_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: ls_def_association_grouping fk_6a465b629388802c; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_def_association_grouping
    ADD CONSTRAINT fk_6a465b629388802c FOREIGN KEY (ls_doc_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: salt_change fk_8427c157895648bc; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_change
    ADD CONSTRAINT fk_8427c157895648bc FOREIGN KEY (doc_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: salt_change fk_8427c157a76ed395; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_change
    ADD CONSTRAINT fk_8427c157a76ed395 FOREIGN KEY (user_id) REFERENCES cftf.salt_user(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: salt_user_doc_acl fk_85c83e4a895648bc; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_user_doc_acl
    ADD CONSTRAINT fk_85c83e4a895648bc FOREIGN KEY (doc_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: salt_user_doc_acl fk_85c83e4aa76ed395; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_user_doc_acl
    ADD CONSTRAINT fk_85c83e4aa76ed395 FOREIGN KEY (user_id) REFERENCES cftf.salt_user(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: rubric_criterion fk_98e476f9a29ec0fc; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.rubric_criterion
    ADD CONSTRAINT fk_98e476f9a29ec0fc FOREIGN KEY (rubric_id) REFERENCES cftf.rubric(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: rubric_criterion fk_98e476f9e27a1fd2; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.rubric_criterion
    ADD CONSTRAINT fk_98e476f9e27a1fd2 FOREIGN KEY (ls_item_id) REFERENCES cftf.ls_item(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_doc fk_9ae8cf1f26ef07c9; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc
    ADD CONSTRAINT fk_9ae8cf1f26ef07c9 FOREIGN KEY (licence_id) REFERENCES cftf.ls_def_licence(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_doc fk_9ae8cf1f3c4c17b2; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc
    ADD CONSTRAINT fk_9ae8cf1f3c4c17b2 FOREIGN KEY (frameworktype_id) REFERENCES cftf.framework_type(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_doc fk_9ae8cf1fa76ed395; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc
    ADD CONSTRAINT fk_9ae8cf1fa76ed395 FOREIGN KEY (user_id) REFERENCES cftf.salt_user(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_doc fk_9ae8cf1ff4837c1b; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc
    ADD CONSTRAINT fk_9ae8cf1ff4837c1b FOREIGN KEY (org_id) REFERENCES cftf.salt_org(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_doc fk_9ae8cf1ff66a84b6; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc
    ADD CONSTRAINT fk_9ae8cf1ff66a84b6 FOREIGN KEY (mirrored_framework_id) REFERENCES cftf.mirror_framework(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_association fk_a84022d434c423c4; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_association
    ADD CONSTRAINT fk_a84022d434c423c4 FOREIGN KEY (origin_lsdoc_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE SET NULL;


--
-- Name: ls_association fk_a84022d44c0c393b; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_association
    ADD CONSTRAINT fk_a84022d44c0c393b FOREIGN KEY (origin_lsitem_id) REFERENCES cftf.ls_item(id) ON UPDATE RESTRICT ON DELETE SET NULL;


--
-- Name: ls_association fk_a84022d459c28905; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_association
    ADD CONSTRAINT fk_a84022d459c28905 FOREIGN KEY (destination_lsdoc_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE SET NULL;


--
-- Name: ls_association fk_a84022d45be201d2; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_association
    ADD CONSTRAINT fk_a84022d45be201d2 FOREIGN KEY (assoc_group_id) REFERENCES cftf.ls_def_association_grouping(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_association fk_a84022d49388802c; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_association
    ADD CONSTRAINT fk_a84022d49388802c FOREIGN KEY (ls_doc_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_association fk_a84022d4a002cdb7; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_association
    ADD CONSTRAINT fk_a84022d4a002cdb7 FOREIGN KEY (destination_lsitem_id) REFERENCES cftf.ls_item(id) ON UPDATE RESTRICT ON DELETE SET NULL;


--
-- Name: ls_item fk_d8d0249826ef07c9; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_item
    ADD CONSTRAINT fk_d8d0249826ef07c9 FOREIGN KEY (licence_id) REFERENCES cftf.ls_def_licence(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_item fk_d8d024989388802c; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_item
    ADD CONSTRAINT fk_d8d024989388802c FOREIGN KEY (ls_doc_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_item fk_d8d02498ce11aac7; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_item
    ADD CONSTRAINT fk_d8d02498ce11aac7 FOREIGN KEY (item_type_id) REFERENCES cftf.ls_def_item_type(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_doc_subject fk_d9a8d91923edc87; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc_subject
    ADD CONSTRAINT fk_d9a8d91923edc87 FOREIGN KEY (subject_id) REFERENCES cftf.ls_def_subject(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_doc_subject fk_d9a8d9199388802c; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_doc_subject
    ADD CONSTRAINT fk_d9a8d9199388802c FOREIGN KEY (ls_doc_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: mirror_server fk_f43f213f41e8b2e5; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.mirror_server
    ADD CONSTRAINT fk_f43f213f41e8b2e5 FOREIGN KEY (credentials_id) REFERENCES cftf.mirror_oauth(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: mirror_framework fk_f6bdd7191844e6b7; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.mirror_framework
    ADD CONSTRAINT fk_f6bdd7191844e6b7 FOREIGN KEY (server_id) REFERENCES cftf.mirror_server(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: salt_user fk_f9577392f4837c1b; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.salt_user
    ADD CONSTRAINT fk_f9577392f4837c1b FOREIGN KEY (org_id) REFERENCES cftf.salt_org(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: import_logs fk_f9c9dbaca4353f8c; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.import_logs
    ADD CONSTRAINT fk_f9c9dbaca4353f8c FOREIGN KEY (ls_doc_id) REFERENCES cftf.ls_doc(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: rubric_criterion_level fk_fec0421697766307; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.rubric_criterion_level
    ADD CONSTRAINT fk_fec0421697766307 FOREIGN KEY (criterion_id) REFERENCES cftf.rubric_criterion(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_item_concept fk_ff2e1b51e27a1fd2; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_item_concept
    ADD CONSTRAINT fk_ff2e1b51e27a1fd2 FOREIGN KEY (ls_item_id) REFERENCES cftf.ls_item(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: ls_item_concept fk_ff2e1b51f909284e; Type: FK CONSTRAINT; Schema: cftf; Owner: cftf
--

ALTER TABLE ONLY cftf.ls_item_concept
    ADD CONSTRAINT fk_ff2e1b51f909284e FOREIGN KEY (concept_id) REFERENCES cftf.ls_def_concept(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

