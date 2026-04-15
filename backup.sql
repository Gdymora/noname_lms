--
-- PostgreSQL database dump
--

\restrict emg5BjKLnUFgo5fcVV7O6lGDgcogYnogbVbJINsboAFyxTndFTwy9jMJYlTondk

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO sail;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO sail;

--
-- Name: content_blocks; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.content_blocks (
    id bigint NOT NULL,
    lesson_id bigint NOT NULL,
    type character varying(255) NOT NULL,
    content jsonb NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    is_visible boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.content_blocks OWNER TO sail;

--
-- Name: content_blocks_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.content_blocks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.content_blocks_id_seq OWNER TO sail;

--
-- Name: content_blocks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.content_blocks_id_seq OWNED BY public.content_blocks.id;


--
-- Name: course_modules; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.course_modules (
    id bigint NOT NULL,
    course_id bigint NOT NULL,
    parent_id bigint,
    title character varying(255) NOT NULL,
    description text,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.course_modules OWNER TO sail;

--
-- Name: course_modules_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.course_modules_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.course_modules_id_seq OWNER TO sail;

--
-- Name: course_modules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.course_modules_id_seq OWNED BY public.course_modules.id;


--
-- Name: courses; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.courses (
    id bigint NOT NULL,
    teacher_id bigint NOT NULL,
    title character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    thumbnail_url character varying(255),
    price integer DEFAULT 0 NOT NULL,
    starts_at timestamp(0) without time zone,
    ends_at timestamp(0) without time zone,
    is_published boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.courses OWNER TO sail;

--
-- Name: courses_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.courses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.courses_id_seq OWNER TO sail;

--
-- Name: courses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.courses_id_seq OWNED BY public.courses.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO sail;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO sail;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: homework_submissions; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.homework_submissions (
    id bigint NOT NULL,
    homework_id bigint NOT NULL,
    student_id bigint NOT NULL,
    curator_id bigint,
    content jsonb NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    curator_comment text,
    grade_percent numeric(5,2),
    reviewed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.homework_submissions OWNER TO sail;

--
-- Name: homework_submissions_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.homework_submissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.homework_submissions_id_seq OWNER TO sail;

--
-- Name: homework_submissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.homework_submissions_id_seq OWNED BY public.homework_submissions.id;


--
-- Name: homeworks; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.homeworks (
    id bigint NOT NULL,
    lesson_id bigint NOT NULL,
    description text NOT NULL,
    submission_fields jsonb,
    is_required boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.homeworks OWNER TO sail;

--
-- Name: homeworks_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.homeworks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.homeworks_id_seq OWNER TO sail;

--
-- Name: homeworks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.homeworks_id_seq OWNED BY public.homeworks.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO sail;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO sail;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO sail;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: lesson_user; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.lesson_user (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    lesson_id bigint NOT NULL,
    unlocked_at timestamp(0) without time zone,
    completed_at timestamp(0) without time zone
);


ALTER TABLE public.lesson_user OWNER TO sail;

--
-- Name: lesson_user_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.lesson_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lesson_user_id_seq OWNER TO sail;

--
-- Name: lesson_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.lesson_user_id_seq OWNED BY public.lesson_user.id;


--
-- Name: lessons; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.lessons (
    id bigint NOT NULL,
    module_id bigint NOT NULL,
    title character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    is_stop_lesson boolean DEFAULT false NOT NULL,
    duration_minutes integer DEFAULT 0 NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.lessons OWNER TO sail;

--
-- Name: lessons_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.lessons_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lessons_id_seq OWNER TO sail;

--
-- Name: lessons_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.lessons_id_seq OWNED BY public.lessons.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO sail;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO sail;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO sail;

--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO sail;

--
-- Name: order_notes; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.order_notes (
    id bigint NOT NULL,
    order_id bigint NOT NULL,
    user_id bigint NOT NULL,
    content text NOT NULL,
    is_private boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.order_notes OWNER TO sail;

--
-- Name: order_notes_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.order_notes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.order_notes_id_seq OWNER TO sail;

--
-- Name: order_notes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.order_notes_id_seq OWNED BY public.order_notes.id;


--
-- Name: orders; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.orders (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    course_id bigint NOT NULL,
    manager_id bigint,
    status character varying(255) DEFAULT 'new'::character varying NOT NULL,
    amount integer NOT NULL,
    history_log jsonb,
    paid_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.orders OWNER TO sail;

--
-- Name: orders_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.orders_id_seq OWNER TO sail;

--
-- Name: orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.orders_id_seq OWNED BY public.orders.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO sail;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO sail;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO sail;

--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO sail;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO sail;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO sail;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO sail;

--
-- Name: system_settings; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.system_settings (
    id bigint NOT NULL,
    "group" character varying(255) NOT NULL,
    key character varying(255) NOT NULL,
    payload jsonb,
    is_locked boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.system_settings OWNER TO sail;

--
-- Name: system_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.system_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.system_settings_id_seq OWNER TO sail;

--
-- Name: system_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.system_settings_id_seq OWNED BY public.system_settings.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    avatar_url character varying(255),
    last_seen_at timestamp(0) without time zone,
    is_active boolean DEFAULT true NOT NULL,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO sail;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO sail;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: content_blocks id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.content_blocks ALTER COLUMN id SET DEFAULT nextval('public.content_blocks_id_seq'::regclass);


--
-- Name: course_modules id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.course_modules ALTER COLUMN id SET DEFAULT nextval('public.course_modules_id_seq'::regclass);


--
-- Name: courses id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.courses ALTER COLUMN id SET DEFAULT nextval('public.courses_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: homework_submissions id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.homework_submissions ALTER COLUMN id SET DEFAULT nextval('public.homework_submissions_id_seq'::regclass);


--
-- Name: homeworks id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.homeworks ALTER COLUMN id SET DEFAULT nextval('public.homeworks_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: lesson_user id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.lesson_user ALTER COLUMN id SET DEFAULT nextval('public.lesson_user_id_seq'::regclass);


--
-- Name: lessons id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.lessons ALTER COLUMN id SET DEFAULT nextval('public.lessons_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: order_notes id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.order_notes ALTER COLUMN id SET DEFAULT nextval('public.order_notes_id_seq'::regclass);


--
-- Name: orders id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.orders ALTER COLUMN id SET DEFAULT nextval('public.orders_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: system_settings id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.system_settings ALTER COLUMN id SET DEFAULT nextval('public.system_settings_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer	i:1763908252;	1763908252
laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab	i:1;	1763908252
laravel-cache-spatie.permission.cache	a:3:{s:5:"alias";a:0:{}s:11:"permissions";a:0:{}s:5:"roles";a:0:{}}	1763999856
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: content_blocks; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.content_blocks (id, lesson_id, type, content, sort_order, is_visible, created_at, updated_at) FROM stdin;
1	1	video_youtube	{"url": "https://www.youtube.com/watch?v=qAySSqZcJMw"}	1	t	2025-11-23 10:36:33	2025-11-23 10:36:33
2	1	text	{"html": "<h2>Тут будет какой-то текст</h2><p><strong>Тут какой-то другой текст</strong></p>"}	2	t	2025-11-23 10:36:33	2025-11-23 10:36:33
3	2	text	{"html": "<h2>Тут заголовок</h2><p>Тут текст <strong>жирный</strong></p>"}	1	t	2025-11-23 12:28:40	2025-11-23 12:28:40
4	2	video_youtube	{"url": "https://www.youtube.com/watch?v=pTnrK4L-L9M"}	2	t	2025-11-23 12:28:40	2025-11-23 12:28:40
5	3	text	{"html": "<p>ваыдаодыоадылоаыдлао</p>"}	1	t	2025-11-23 13:41:40	2025-11-23 13:41:40
6	4	text	{"html": "<h2>Заголовок</h2><p><strong>Тут жирный текст</strong></p><p><span style=\\"text-decoration: underline;\\">Тут подчеркнутый</span></p><blockquote>Умная цитата</blockquote>"}	1	t	2025-11-23 14:23:52	2025-11-23 14:23:52
7	5	text	{"html": "<p>ываыфва</p>"}	1	t	2025-11-23 15:17:07	2025-11-23 15:17:07
8	6	text	{"html": "<p>выафыавыфва</p>"}	1	t	2025-11-23 17:00:02	2025-11-23 17:00:02
\.


--
-- Data for Name: course_modules; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.course_modules (id, course_id, parent_id, title, description, sort_order, created_at, updated_at) FROM stdin;
1	1	\N	Знакомство	Тестовое описание	0	2025-11-23 10:11:58	2025-11-23 10:11:58
2	1	\N	Первые уроки	Тестовое описание	0	2025-11-23 10:13:22	2025-11-23 10:13:22
5	2	\N	Модуль 1	\N	0	2025-11-23 12:27:34	2025-11-23 12:27:34
6	3	\N	Вводный модуль	\N	0	2025-11-23 14:21:45	2025-11-23 14:21:45
7	5	\N	Модуль 1	\N	0	2025-11-23 16:10:56	2025-11-23 16:10:56
\.


--
-- Data for Name: courses; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.courses (id, teacher_id, title, slug, description, thumbnail_url, price, starts_at, ends_at, is_published, created_at, updated_at, deleted_at) FROM stdin;
1	1	Тестовый курс	testovyi-kurs	Это тестовый курс который мы тестово создали для тестового прогона базы	\N	1000000	2025-11-01 02:00:31	2025-12-07 01:23:54	t	2025-11-22 22:24:03	2025-11-23 12:13:49	\N
2	1	Бесплатный курс	besplatnyi-kurs	Бесплатный курс	\N	0	\N	\N	t	2025-11-23 12:21:06	2025-11-23 12:21:06	\N
3	1	PRO Коды на GetCourse	pro-kody-na-getcourse	Какая-то чепуха которая никому нахуй и бесплатно не всралась	course-thumbnails/01KARHY5PSGBPNN2TD9TP4KSVY.jpg	0	\N	\N	t	2025-11-23 14:20:46	2025-11-23 14:29:56	\N
5	2	Тестовый курс от учителя	testovyi-kurs-ot-ucitelia	ыдаоывдлао	\N	0	\N	\N	t	2025-11-23 16:01:06	2025-11-23 16:01:06	\N
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: homework_submissions; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.homework_submissions (id, homework_id, student_id, curator_id, content, status, curator_comment, grade_percent, reviewed_at, created_at, updated_at) FROM stdin;
2	5	1	\N	{"Обьяснить что такое что-то": "фыаыфваыфа", "Прикрепить ссылку на гугл диск": "ывавафываывафы"}	approved	Член	88.00	\N	2025-11-23 14:26:42	2025-11-23 14:27:15
1	3	1	\N	{"sdfdsf": "sfsdfsdf"}	approved	dfdfdf	100.00	\N	2025-11-23 13:59:14	2025-11-23 15:17:21
4	6	1	\N	{"21424": "куаываыва"}	approved	яапролд	50.00	\N	2025-11-23 15:21:22	2025-11-23 15:23:13
\.


--
-- Data for Name: homeworks; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.homeworks (id, lesson_id, description, submission_fields, is_required, created_at, updated_at) FROM stdin;
1	1	<p>Скиньте ссылку на ваш проект</p>	[{"type": "url", "label": "Ссылка на ваш проект", "required": true}]	t	2025-11-23 10:39:30	2025-11-23 10:39:30
3	2	<p>dsfsdf</p>	[{"type": "text", "label": "sdfdsf", "required": true}]	t	2025-11-23 13:40:49	2025-11-23 13:40:49
4	3	<p>аываыфа</p>	[{"type": "string", "label": "фыафыва", "required": true}]	t	2025-11-23 13:42:14	2025-11-23 13:42:14
5	4	<p>Тут какое-то задание</p>	[{"type": "url", "label": "Прикрепить ссылку на гугл диск", "required": true}, {"type": "text", "label": "Обьяснить что такое что-то", "required": true}]	t	2025-11-23 14:26:04	2025-11-23 14:26:04
6	5	<p>214234</p>	[{"type": "text", "label": "21424", "required": true}]	t	2025-11-23 15:18:29	2025-11-23 15:18:29
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: lesson_user; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.lesson_user (id, user_id, lesson_id, unlocked_at, completed_at) FROM stdin;
1	1	2	\N	2025-11-23 14:19:08
2	1	4	\N	2025-11-23 15:22:24
3	1	5	\N	2025-11-23 15:22:26
4	1	6	\N	2025-11-23 17:23:14
\.


--
-- Data for Name: lessons; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.lessons (id, module_id, title, slug, is_stop_lesson, duration_minutes, sort_order, created_at, updated_at, deleted_at) FROM stdin;
1	1	Тестовый урок	testovyi-urok	f	15	0	2025-11-23 10:36:33	2025-11-23 10:36:33	\N
2	5	Урок 1	urok-1	f	15	0	2025-11-23 12:28:40	2025-11-23 12:28:40	\N
3	5	Тестовый урок 2	testovyi-urok-2	f	15	0	2025-11-23 13:41:40	2025-11-23 13:41:40	\N
4	6	Знакомство	znakomstvo	f	15	0	2025-11-23 14:23:52	2025-11-23 14:23:52	\N
5	6	После знакомства	posle-znakomstva	f	15	0	2025-11-23 15:17:07	2025-11-23 15:17:07	\N
6	7	Урок 1	urok-1	f	15	0	2025-11-23 17:00:02	2025-11-23 17:00:02	\N
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2025_11_22_220811_create_system_settings_table	1
5	2025_11_22_220824_add_fields_to_users_table	1
6	2025_11_22_220831_create_course_structure_tables	1
7	2025_11_22_220836_create_lessons_and_blocks_tables	1
8	2025_11_22_220841_create_homeworks_and_progress_tables	1
9	2025_11_22_220851_create_orders_table	1
10	2025_11_23_152722_create_permission_tables	2
11	2025_11_23_175701_add_notes_to_orders_table	3
12	2025_11_23_180126_create_order_notes_table	4
\.


--
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
1	App\\Models\\User	1
2	App\\Models\\User	2
\.


--
-- Data for Name: order_notes; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.order_notes (id, order_id, user_id, content, is_private, created_at, updated_at) FROM stdin;
1	4	1	Странный хуй какой-то, админ говорит 	t	2025-11-23 18:08:27	2025-11-23 18:08:27
2	4	1	Совсем ебанулся старый, сам про себя херню пишет	t	2025-11-23 18:08:59	2025-11-23 18:08:59
\.


--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.orders (id, user_id, course_id, manager_id, status, amount, history_log, paid_at, created_at, updated_at) FROM stdin;
1	1	1	\N	new	1000000	{"ip": "172.18.0.1", "action": "created_by_student"}	\N	2025-11-23 12:20:00	2025-11-23 12:20:00
2	1	2	\N	paid	0	{"ip": "172.18.0.1", "action": "created_by_student"}	2025-11-23 12:21:17	2025-11-23 12:21:17	2025-11-23 12:21:17
3	1	3	\N	paid	0	{"ip": "172.18.0.1", "action": "created_by_student"}	2025-11-23 14:26:23	2025-11-23 14:26:23	2025-11-23 14:26:23
4	1	5	\N	paid	0	{"ip": "172.18.0.1", "action": "created_by_student"}	2025-11-23 17:23:07	2025-11-23 17:23:07	2025-11-23 18:00:11
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
1	Super Admin	web	2025-11-23 15:29:40	2025-11-23 15:29:40
2	Teacher	web	2025-11-23 15:29:40	2025-11-23 15:29:40
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
TxTt362D6gjuf75wZdCEMQ86XyMo9dLRsUYxOxe0	1	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMVl3TkdSUzRRYVgxRG13bDZRQzZaU0RMTWR4bDc5RVdNRnFVWG5JMCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo0ODoiaHR0cDovL2xvY2FsaG9zdC9sZWFybmluZy9iZXNwbGF0bnlpLWt1cnMvdXJvay0xIjtzOjU6InJvdXRlIjtzOjE1OiJsZWFybmluZy5sZXNzb24iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1763980777
7PndCJJsm2EaFk85IZtQ1ofUKcIqNZW36KYYnzPx	1	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	YTo3OntzOjY6Il90b2tlbiI7czo0MDoiZnlGMWpIVGdrdTI1VEo5S2lialU1aTh1VEI4QmFGQ3g0OTZPS1pLTiI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJFFiOFA1bE1tOWsxcFRMVGNYaXNDS2VoWmo0cDhwR0MzQ2luWG5oOTlSNUlmMVE2NTU3MDhPIjtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyODoiaHR0cDovL2xvY2FsaG9zdC9teS1sZWFybmluZyI7czo1OiJyb3V0ZSI7czoxMToibXkubGVhcm5pbmciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjg6ImZpbGFtZW50IjthOjA6e31zOjY6InRhYmxlcyI7YToxOntzOjQ4OiI2NmM4ZWI3MTMxNDRmY2EzZWJiNDE2NmNkOTQxZjM2Ml90b2dnbGVkX2NvbHVtbnMiO2E6MTp7czoxMDoiY3JlYXRlZF9hdCI7YjowO319fQ==	1763921917
EDCmEhY3WZiZcj2Moz1IGqNTkEs6MQIwZI7dfOSP	2	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	YTo2OntzOjY6Il90b2tlbiI7czo0MDoiRE9kZTVVVkxnQjd6cWJMdjR0d2x6amNpbFBBOEx5NndGUGR4VUg5ZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly9sb2NhbGhvc3QvYWRtaW4vb3JkZXJzIjtzOjU6InJvdXRlIjtzOjM3OiJmaWxhbWVudC5hZG1pbi5yZXNvdXJjZXMub3JkZXJzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJG1abVJpSjQzV2tYSVZINlNHaHRvby5wdHVjMlVHczQzbk94M0J4UDAuV2F2RnhCTFNhdHVxIjtzOjg6ImZpbGFtZW50IjthOjA6e319	1763921325
\.


--
-- Data for Name: system_settings; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.system_settings (id, "group", key, payload, is_locked, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, avatar_url, last_seen_at, is_active, deleted_at) FROM stdin;
2	Teacher	teacher@test.com	\N	$2y$12$mZmRiJ43WkXIVH6SGhtoo.ptuc2UGs43nOx3BxP0.WavFxBLSatuq	\N	2025-11-23 15:57:22	2025-11-23 15:57:22	\N	\N	t	\N
1	Admin	i@aifire.ru	\N	$2y$12$Qb8P5lMm9k1pTLTcXisCKehZj4p8pGC3CinXnh99R5If1Q655708O	aCIlnyjtR06hwmjTl1h7M3pBYy8axq3i2L47BMMIbYnntZhRnNCE8NhhdLfw	2025-11-22 22:22:32	2025-11-23 17:12:43	avatars/CYQVdJ0vxTlRNI74cKTLJUFYqjfTEj8famT6iF00.jpg	\N	t	\N
\.


--
-- Name: content_blocks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.content_blocks_id_seq', 8, true);


--
-- Name: course_modules_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.course_modules_id_seq', 7, true);


--
-- Name: courses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.courses_id_seq', 5, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: homework_submissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.homework_submissions_id_seq', 4, true);


--
-- Name: homeworks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.homeworks_id_seq', 6, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.jobs_id_seq', 5, true);


--
-- Name: lesson_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.lesson_user_id_seq', 4, true);


--
-- Name: lessons_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.lessons_id_seq', 6, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.migrations_id_seq', 12, true);


--
-- Name: order_notes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.order_notes_id_seq', 2, true);


--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.orders_id_seq', 5, true);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.permissions_id_seq', 1, false);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.roles_id_seq', 2, true);


--
-- Name: system_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.system_settings_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.users_id_seq', 2, true);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: content_blocks content_blocks_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.content_blocks
    ADD CONSTRAINT content_blocks_pkey PRIMARY KEY (id);


--
-- Name: course_modules course_modules_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.course_modules
    ADD CONSTRAINT course_modules_pkey PRIMARY KEY (id);


--
-- Name: courses courses_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.courses
    ADD CONSTRAINT courses_pkey PRIMARY KEY (id);


--
-- Name: courses courses_slug_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.courses
    ADD CONSTRAINT courses_slug_unique UNIQUE (slug);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: homework_submissions homework_submissions_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.homework_submissions
    ADD CONSTRAINT homework_submissions_pkey PRIMARY KEY (id);


--
-- Name: homeworks homeworks_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.homeworks
    ADD CONSTRAINT homeworks_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: lesson_user lesson_user_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.lesson_user
    ADD CONSTRAINT lesson_user_pkey PRIMARY KEY (id);


--
-- Name: lesson_user lesson_user_user_id_lesson_id_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.lesson_user
    ADD CONSTRAINT lesson_user_user_id_lesson_id_unique UNIQUE (user_id, lesson_id);


--
-- Name: lessons lessons_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT lessons_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: order_notes order_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.order_notes
    ADD CONSTRAINT order_notes_pkey PRIMARY KEY (id);


--
-- Name: orders orders_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: system_settings system_settings_key_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.system_settings
    ADD CONSTRAINT system_settings_key_unique UNIQUE (key);


--
-- Name: system_settings system_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.system_settings
    ADD CONSTRAINT system_settings_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: content_blocks_type_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX content_blocks_type_index ON public.content_blocks USING btree (type);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: system_settings_group_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX system_settings_group_index ON public.system_settings USING btree ("group");


--
-- Name: content_blocks content_blocks_lesson_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.content_blocks
    ADD CONSTRAINT content_blocks_lesson_id_foreign FOREIGN KEY (lesson_id) REFERENCES public.lessons(id) ON DELETE CASCADE;


--
-- Name: course_modules course_modules_course_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.course_modules
    ADD CONSTRAINT course_modules_course_id_foreign FOREIGN KEY (course_id) REFERENCES public.courses(id) ON DELETE CASCADE;


--
-- Name: course_modules course_modules_parent_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.course_modules
    ADD CONSTRAINT course_modules_parent_id_foreign FOREIGN KEY (parent_id) REFERENCES public.course_modules(id) ON DELETE CASCADE;


--
-- Name: courses courses_teacher_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.courses
    ADD CONSTRAINT courses_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: homework_submissions homework_submissions_curator_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.homework_submissions
    ADD CONSTRAINT homework_submissions_curator_id_foreign FOREIGN KEY (curator_id) REFERENCES public.users(id);


--
-- Name: homework_submissions homework_submissions_homework_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.homework_submissions
    ADD CONSTRAINT homework_submissions_homework_id_foreign FOREIGN KEY (homework_id) REFERENCES public.homeworks(id) ON DELETE CASCADE;


--
-- Name: homework_submissions homework_submissions_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.homework_submissions
    ADD CONSTRAINT homework_submissions_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: homeworks homeworks_lesson_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.homeworks
    ADD CONSTRAINT homeworks_lesson_id_foreign FOREIGN KEY (lesson_id) REFERENCES public.lessons(id) ON DELETE CASCADE;


--
-- Name: lesson_user lesson_user_lesson_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.lesson_user
    ADD CONSTRAINT lesson_user_lesson_id_foreign FOREIGN KEY (lesson_id) REFERENCES public.lessons(id) ON DELETE CASCADE;


--
-- Name: lesson_user lesson_user_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.lesson_user
    ADD CONSTRAINT lesson_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: lessons lessons_module_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT lessons_module_id_foreign FOREIGN KEY (module_id) REFERENCES public.course_modules(id) ON DELETE CASCADE;


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: order_notes order_notes_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.order_notes
    ADD CONSTRAINT order_notes_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;


--
-- Name: order_notes order_notes_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.order_notes
    ADD CONSTRAINT order_notes_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: orders orders_course_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_course_id_foreign FOREIGN KEY (course_id) REFERENCES public.courses(id) ON DELETE CASCADE;


--
-- Name: orders orders_manager_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_manager_id_foreign FOREIGN KEY (manager_id) REFERENCES public.users(id);


--
-- Name: orders orders_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict emg5BjKLnUFgo5fcVV7O6lGDgcogYnogbVbJINsboAFyxTndFTwy9jMJYlTondk

