--
-- PostgreSQL database dump
--

\restrict LWiKWRUSrZVoQcN6G04GQWFvOM0xncCiA9jdHJYlwOXpRmZYgl8Ir7HaMCztJdS

-- Dumped from database version 17.6
-- Dumped by pg_dump version 18.4

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

--
-- Data for Name: audit_log_entries; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.audit_log_entries (instance_id, id, payload, created_at, ip_address) FROM stdin;
\.


--
-- Data for Name: custom_oauth_providers; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.custom_oauth_providers (id, provider_type, identifier, name, client_id, client_secret, acceptable_client_ids, scopes, pkce_enabled, attribute_mapping, authorization_params, enabled, email_optional, issuer, discovery_url, skip_nonce_check, cached_discovery, discovery_cached_at, authorization_url, token_url, userinfo_url, jwks_uri, created_at, updated_at, custom_claims_allowlist) FROM stdin;
\.


--
-- Data for Name: flow_state; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.flow_state (id, user_id, auth_code, code_challenge_method, code_challenge, provider_type, provider_access_token, provider_refresh_token, created_at, updated_at, authentication_method, auth_code_issued_at, invite_token, referrer, oauth_client_state_id, linking_target_id, email_optional) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.users (instance_id, id, aud, role, email, encrypted_password, email_confirmed_at, invited_at, confirmation_token, confirmation_sent_at, recovery_token, recovery_sent_at, email_change_token_new, email_change, email_change_sent_at, last_sign_in_at, raw_app_meta_data, raw_user_meta_data, is_super_admin, created_at, updated_at, phone, phone_confirmed_at, phone_change, phone_change_token, phone_change_sent_at, email_change_token_current, email_change_confirm_status, banned_until, reauthentication_token, reauthentication_sent_at, is_sso_user, deleted_at, is_anonymous) FROM stdin;
\.


--
-- Data for Name: identities; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.identities (provider_id, user_id, identity_data, provider, last_sign_in_at, created_at, updated_at, id) FROM stdin;
\.


--
-- Data for Name: instances; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.instances (id, uuid, raw_base_config, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: oauth_clients; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.oauth_clients (id, client_secret_hash, registration_type, redirect_uris, grant_types, client_name, client_uri, logo_uri, created_at, updated_at, deleted_at, client_type, token_endpoint_auth_method) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.sessions (id, user_id, created_at, updated_at, factor_id, aal, not_after, refreshed_at, user_agent, ip, tag, oauth_client_id, refresh_token_hmac_key, refresh_token_counter, scopes) FROM stdin;
\.


--
-- Data for Name: mfa_amr_claims; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.mfa_amr_claims (session_id, created_at, updated_at, authentication_method, id) FROM stdin;
\.


--
-- Data for Name: mfa_factors; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.mfa_factors (id, user_id, friendly_name, factor_type, status, created_at, updated_at, secret, phone, last_challenged_at, web_authn_credential, web_authn_aaguid, last_webauthn_challenge_data) FROM stdin;
\.


--
-- Data for Name: mfa_challenges; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.mfa_challenges (id, factor_id, created_at, verified_at, ip_address, otp_code, web_authn_session_data) FROM stdin;
\.


--
-- Data for Name: oauth_authorizations; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.oauth_authorizations (id, authorization_id, client_id, user_id, redirect_uri, scope, state, resource, code_challenge, code_challenge_method, response_type, status, authorization_code, created_at, expires_at, approved_at, nonce) FROM stdin;
\.


--
-- Data for Name: oauth_client_states; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.oauth_client_states (id, provider_type, code_verifier, created_at) FROM stdin;
\.


--
-- Data for Name: oauth_consents; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.oauth_consents (id, user_id, client_id, scopes, granted_at, revoked_at) FROM stdin;
\.


--
-- Data for Name: one_time_tokens; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.one_time_tokens (id, user_id, token_type, token_hash, relates_to, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: refresh_tokens; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.refresh_tokens (instance_id, id, token, user_id, revoked, created_at, updated_at, parent, session_id) FROM stdin;
\.


--
-- Data for Name: sso_providers; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.sso_providers (id, resource_id, created_at, updated_at, disabled) FROM stdin;
\.


--
-- Data for Name: saml_providers; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.saml_providers (id, sso_provider_id, entity_id, metadata_xml, metadata_url, attribute_mapping, created_at, updated_at, name_id_format) FROM stdin;
\.


--
-- Data for Name: saml_relay_states; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.saml_relay_states (id, sso_provider_id, request_id, for_email, redirect_to, created_at, updated_at, flow_state_id) FROM stdin;
\.


--
-- Data for Name: schema_migrations; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.schema_migrations (version) FROM stdin;
20171026211738
20171026211808
20171026211834
20180103212743
20180108183307
20180119214651
20180125194653
00
20210710035447
20210722035447
20210730183235
20210909172000
20210927181326
20211122151130
20211124214934
20211202183645
20220114185221
20220114185340
20220224000811
20220323170000
20220429102000
20220531120530
20220614074223
20220811173540
20221003041349
20221003041400
20221011041400
20221020193600
20221021073300
20221021082433
20221027105023
20221114143122
20221114143410
20221125140132
20221208132122
20221215195500
20221215195800
20221215195900
20230116124310
20230116124412
20230131181311
20230322519590
20230402418590
20230411005111
20230508135423
20230523124323
20230818113222
20230914180801
20231027141322
20231114161723
20231117164230
20240115144230
20240214120130
20240306115329
20240314092811
20240427152123
20240612123726
20240729123726
20240802193726
20240806073726
20241009103726
20250717082212
20250731150234
20250804100000
20250901200500
20250903112500
20250904133000
20250925093508
20251007112900
20251104100000
20251111201300
20251201000000
20260115000000
20260121000000
20260219120000
20260302000000
20260625000000
\.


--
-- Data for Name: sso_domains; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.sso_domains (id, sso_provider_id, domain, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: webauthn_challenges; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.webauthn_challenges (id, user_id, challenge_type, session_data, created_at, expires_at) FROM stdin;
\.


--
-- Data for Name: webauthn_credentials; Type: TABLE DATA; Schema: auth; Owner: -
--

COPY auth.webauthn_credentials (id, user_id, credential_id, public_key, attestation_type, aaguid, sign_count, transports, backup_eligible, backed_up, friendly_name, created_at, updated_at, last_used_at) FROM stdin;
\.


--
-- Data for Name: assessment_categories; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.assessment_categories (id, name, created_at, updated_at) FROM stdin;
1	Written Work	2026-08-17 18:41:35	2026-08-17 18:41:35
2	Performance Task	2026-08-17 18:41:38	2026-08-17 18:41:38
3	Term Assessment	2026-08-17 18:41:38	2026-08-17 18:41:38
\.


--
-- Data for Name: grading_periods; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.grading_periods (id, name, sequence, is_active, created_at, updated_at, period_type) FROM stdin;
1	Term 1	1	t	2026-08-07 17:29:30	2026-08-07 17:29:30	trimester
2	Term 2	2	t	2026-08-07 17:29:30	2026-08-07 17:29:30	trimester
3	Term 3	3	t	2026-08-07 17:29:30	2026-08-07 17:29:30	trimester
4	Term 4	4	t	2026-08-07 17:29:30	2026-08-07 17:29:30	trimester
\.


--
-- Data for Name: school_years; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.school_years (id, school_year, is_active, created_at, updated_at) FROM stdin;
7	2026-2027	t	2026-08-08 12:31:44	2026-08-08 12:31:44
8	2025-2026	t	2026-08-08 22:24:32	2026-08-08 22:24:32
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, first_name, last_name, role, status, email, email_verified_at, password, remember_token, deleted_at, created_at, updated_at, employee_id) FROM stdin;
57	Arriane	Estrada	teacher	active	arriane.estrada.dev	\N	$2y$12$kZwoZ7cGGRE4qOTYtZt8ze3/oNbxoPJnWk4.USxDd1I46nZs78KLO	\N	\N	2026-08-09 18:45:32	2026-08-09 18:45:32	\N
69	Fixedtest	Fixedtest	teacher	active	fixedtest.1786294146765@example.com	\N	$2y$12$hXvDgrxixKg7Lj3n73b8w.0MySgLuawLvjdutQvIfeKcJZgX1mCwm	\N	\N	2026-08-10 00:49:09	2026-08-10 00:49:09	EMP-2026-0069
70	Verifyfix	Verifyfix	teacher	active	verifyfix.1786294158304@example.com	\N	$2y$12$2WCEsmCPhZZYQsKEFz5/iepfbePwvxtww0tuK9QldOl44zIXR5kjC	\N	\N	2026-08-10 00:49:20	2026-08-10 00:49:20	EMP-2026-0070
71	Flowfirstupdated	Flowlast	teacher	inactive	flowtest.1786294176889@example.com	\N	$2y$12$J1/Jqqb1XaI.dcxczi19cuQ/Zkyr6N7ORB8BSRuL.NaaXiYNitMS2	\N	\N	2026-08-10 00:49:39	2026-08-10 00:58:05	EMP-2026-0071
72	Whutwhite	Heckeia	teacher	active	test@gmail.com	\N	$2y$12$l0YBT8110vy/Rw87.qnQxO3r.bzrcFGNjIfwnXVMGBv1Hd6Sp8dA6	\N	\N	2026-08-10 01:13:05	2026-08-10 01:13:05	EMP-2026-0072
58	Test	User	teacher	active	test@example.com	\N	$2y$12$ljMuWOiGFjwj6ZBRhqFa/eohrGyEp6ALORYiPenppug4oPS3er6Pq	\N	2026-08-10 11:11:38	2026-08-09 18:46:58	2026-08-10 11:11:38	EMP-2026-0058
74	Test	Teacher	teacher	active	test.teacher.display@example.com	\N	$2y$12$oFFO1eYrl1eWwCsBlTGFHeKe59kWYENwz9lFGd9FzvbUqsALml47y	\N	2026-08-10 11:12:17	2026-08-10 10:58:19	2026-08-10 11:12:17	EMP-2026-0074
51	Test	Teacher	teacher	active	test.teacher.unique@example.com	\N	$2y$12$jaHW5e8ZQR3IYgWCuBHte.J4k7MeDJv3tD7l9pnfuuK7qG9R3nazu	\N	\N	2026-08-08 17:08:10	2026-08-08 17:08:10	EMP-2026-0051
52	Juan	Dela Cruz	teacher	active	juan.delacruz.test@example.com	\N	$2y$12$RIKK3Q2eyhw1fUxABtBqveBwh4wZys3P4g69fLmISrZV3SUILCmQK	\N	\N	2026-08-08 17:18:29	2026-08-08 17:18:29	EMP-2026-0052
54	Chanyeol	Park	teacher	active	chanyeolpark92@gmail.com	\N	$2y$12$Wy6.fufaU64dcMLIAFb2CuxsWJ5sq8vp6RnpdnW1ox9xlJ1yZvnGK	\N	\N	2026-08-08 17:21:49	2026-08-08 17:21:49	EMP-2026-0054
16	Arriane	Estrada	teacher	active	arriane.estrada.dev@gmail.com	\N	$2y$12$KeIkI1ufL9s/ROEWuxe0L.yWVcIr.BgnSuJqWg.C52ESDhYi1Rf6q	\N	\N	2026-08-07 17:35:25	2026-08-08 17:37:23	\N
59	Locar	Gaming	teacher	active	arriane.estrada.new@example.com	\N	$2y$12$SRio5bs1EMCQts1pl2TyyO9nQ93U/kUvm8GmSh9a7UCQ8TvWJVfPy	\N	\N	2026-08-09 18:56:22	2026-08-09 18:56:22	EMP-2026-0059
56	Trisha	Martinez	teacher	active	trishamaemartinez8@gmail.com	\N	$2y$12$N/9BtTEz0/v2npQlnyCiTe7HSojCv1SBVKIdAlZhSuFbA6zIb6zIO	\N	\N	2026-08-08 21:48:29	2026-08-14 13:18:09	\N
15	CIS	Admin	admin	active	superadmin@cis.edu.ph	2026-08-07 17:35:04	$2y$12$c8oPCYKV8QKe08qKE.vytOlHIeqZk4f.WaoUb9CZ/V8vEqyzLSGIa	ifFYGz4O6NAVC4JYKmdbiFhyyNwWyhlYBqZRKeNdiYPxta3dIMBT7U8M7p7U	\N	2026-08-07 17:35:07	2026-08-07 17:35:07	\N
\.


--
-- Data for Name: teachers; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.teachers (id, user_id, created_at, updated_at, status) FROM stdin;
1	16	2026-08-07 17:35:27	2026-08-07 17:35:27	active
3	51	2026-08-08 17:15:21	2026-08-08 17:15:21	active
4	52	2026-08-08 17:18:31	2026-08-08 17:18:31	active
5	54	2026-08-08 17:21:51	2026-08-08 17:21:51	active
6	56	2026-08-08 21:48:31	2026-08-08 21:48:31	active
7	57	2026-08-09 18:45:35	2026-08-09 18:45:35	active
8	58	2026-08-09 18:47:12	2026-08-09 18:47:12	active
10	59	2026-08-09 18:56:50	2026-08-09 18:56:50	active
11	69	2026-08-10 00:49:10	2026-08-10 00:49:10	active
12	70	2026-08-10 00:49:22	2026-08-10 00:49:22	active
13	71	2026-08-10 00:49:40	2026-08-10 00:58:04	inactive
14	72	2026-08-10 01:13:07	2026-08-10 01:13:07	active
\.


--
-- Data for Name: sections; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.sections (id, name, level, grade_level, advisor_id, created_at, updated_at, status, capacity, session_type) FROM stdin;
5	Anahaw	elementary	1	5	2026-08-11 20:53:35	2026-08-15 12:55:53	active	30	whole_day
4	MAHOGANY	highschool	7	\N	2026-08-10 00:51:16	2026-08-15 12:56:13	active	40	morning
6	AGUINALDO	highschool	8	1	2026-08-14 13:03:55	2026-08-15 12:56:36	active	40	afternoon
3	BONFACIO	highschool	10	6	2026-08-08 22:25:07	2026-08-15 13:24:09	active	40	afternoon
7	Courage	highschool	7	3	2026-08-17 21:30:07	2026-08-17 21:30:07	active	40	morning
\.


--
-- Data for Name: subjects; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.subjects (id, code, name, level, created_at, updated_at) FROM stdin;
1	ENGLISH10	English	hs	2026-08-08 22:25:08	2026-08-08 22:25:08
2	SUB48683	Test Subject 1786294316326	hs	2026-08-10 00:51:58	2026-08-10 00:51:58
\.


--
-- Data for Name: teaching_assignments; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.teaching_assignments (id, teacher_id, subject_id, section_id, school_year_id, status, created_at, updated_at) FROM stdin;
1	6	1	3	8	active	2026-08-08 22:31:32	2026-08-08 22:31:32
2	6	1	4	8	active	2026-08-10 12:17:07	2026-08-10 12:17:07
3	5	1	5	7	active	2026-08-12 20:53:55	2026-08-12 20:53:55
4	1	1	6	7	active	2026-08-14 13:05:33	2026-08-14 13:05:33
5	6	1	6	7	active	2026-08-14 13:19:34	2026-08-14 13:19:34
\.


--
-- Data for Name: assessments; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.assessments (id, teaching_assignment_id, assessment_category_id, grading_period_id, title, total_items, assessment_date, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: students; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.students (id, student_number, first_name, last_name, sex, address, status, created_at, updated_at, lrn, middle_name, suffix, age, birthplace, mother_tongue, ip_ethnic_group, religion) FROM stdin;
2	STU-2026-0002	Arriane	Estrada	female	Pansumaloc	active	2026-08-08 12:36:45	2026-08-08 12:36:45	122120313829	Rueda	\N	\N	\N	\N	\N	\N
35	STU-2026-0035	Marga	Estrada	female	Pansumaloc	active	2026-08-10 00:02:06	2026-08-10 00:02:06	104593493048	Rueda	\N	\N	\N	\N	\N	\N
36	STU-2026-0036	ModalStudFirst	ModalStudLast	female	Test Address	active	2026-08-10 00:43:16	2026-08-10 00:43:16	272104003810	\N	\N	\N	\N	\N	\N	\N
38	STU-2026-0038	SFlow	SFlow	male	Test St	active	2026-08-10 00:50:26	2026-08-10 00:50:26	217715492176	M	\N	\N	\N	\N	\N	\N
55	STU-2026-0055	Lalisa	Manoban	female	Makati Manila, Philippines	active	2026-08-14 13:08:18	2026-08-14 13:08:18	104754100033	\N	\N	\N	\N	\N	\N	\N
39	STU-2026-0039	hdsads	sadsadsds	female	dsadsdsadsd	active	2026-08-10 01:17:07	2026-08-10 01:17:07	3943098402142	dsads	\N	\N	\N	\N	\N	\N
40	STU-2026-0040	RAKUTEN	VIBER	male	Pansumaloc	active	2026-08-12 11:29:54	2026-08-12 11:29:54	105944090030	Rueda	\N	\N	\N	\N	\N	\N
41	STU-2026-0041	Danerie	Del Rosario	male	Manila Philippines	active	2026-08-12 16:52:14	2026-08-12 16:52:14	1049663093430	\N	\N	\N	\N	\N	\N	\N
43	STU-2026-0043	Miguel	Torres	male	123 Manila St	active	2026-08-13 10:15:51	2026-08-13 10:15:51	122120313830	\N	\N	\N	\N	\N	\N	\N
45	STU-2026-0045	Miguel	Torres	male	123 Manila St	active	2026-08-13 10:19:39	2026-08-13 10:19:39	122120313840	\N	\N	\N	\N	\N	\N	\N
46	STU-2026-0046	Isabella	Ramos	female	456 Quezon Ave	active	2026-08-13 10:21:02	2026-08-13 10:21:02	122120313841	\N	\N	\N	\N	\N	\N	\N
47	STU-2026-0047	Gabriel	Fernandez	male	789 Rizal Blvd	active	2026-08-13 10:21:25	2026-08-13 10:21:25	122120313842	\N	\N	\N	\N	\N	\N	\N
48	STU-2026-0048	Sofia	Mendoza	female	321 Bonifacio St	active	2026-08-13 10:22:04	2026-08-13 10:22:04	122120313843	\N	\N	\N	\N	\N	\N	\N
49	STU-2026-0049	Rafael	Villanueva	male	654 Aguinaldo Hwy	active	2026-08-13 10:22:32	2026-08-13 10:22:32	122120313844	\N	\N	\N	\N	\N	\N	\N
50	STU-2026-0050	Camila	Bautista	female	987 Mabini St	active	2026-08-13 10:23:49	2026-08-13 10:23:49	122120313845	\N	\N	\N	\N	\N	\N	\N
51	STU-2026-0051	Diego	Salazar	male	147 P. Burgos St	active	2026-08-13 10:24:43	2026-08-13 10:24:43	122120313846	\N	\N	\N	\N	\N	\N	\N
52	STU-2026-0052	Valentina	Aquino	female	258 Taft Ave	active	2026-08-13 10:25:32	2026-08-13 10:25:32	122120313847	\N	\N	\N	\N	\N	\N	\N
53	STU-2026-0053	Mateo	Castillo	male	369 Del Pilar St	active	2026-08-13 10:26:06	2026-08-13 10:26:06	122120313848	\N	\N	\N	\N	\N	\N	\N
54	STU-2026-0054	Elena	Navarro	female	710 Ayala Blvd	active	2026-08-13 10:26:31	2026-08-13 10:26:31	122120313849	\N	\N	\N	\N	\N	\N	\N
4	STU-2026-0004	Juan	Dela Cruz	male	123 Sample Street, City	active	2026-08-08 22:32:22	2026-08-13 11:21:45	1234567893772	\N	\N	\N	\N	\N	\N	\N
5	STU-2026-0005	Juan	Dela Cruz	male	123 Sample Street, City	active	2026-08-08 22:33:09	2026-08-13 11:21:45	1234567897005	\N	\N	\N	\N	\N	\N	\N
6	STU-2026-0006	Maria	Santos	female	123 Sample Street, City	active	2026-08-08 22:33:11	2026-08-13 11:21:46	1234567891483	\N	\N	\N	\N	\N	\N	\N
7	STU-2026-0007	Pedro	Gonzalez	male	123 Sample Street, City	active	2026-08-08 22:33:13	2026-08-13 11:21:46	1234567895629	\N	\N	\N	\N	\N	\N	\N
8	STU-2026-0008	Ana	Reyes	female	123 Sample Street, City	active	2026-08-08 22:33:15	2026-08-13 11:21:47	1234567893248	\N	\N	\N	\N	\N	\N	\N
9	STU-2026-0009	Carlos	Lopez	male	123 Sample Street, City	active	2026-08-08 22:33:17	2026-08-13 11:21:48	1234567893057	\N	\N	\N	\N	\N	\N	\N
10	STU-2026-0010	Juan	Dela Cruz	male	123 Sample Street, City	active	2026-08-08 22:33:41	2026-08-13 11:21:48	1234567891027	\N	\N	\N	\N	\N	\N	\N
11	STU-2026-0011	Maria	Santos	female	123 Sample Street, City	active	2026-08-08 22:33:43	2026-08-13 11:21:49	1234567891888	\N	\N	\N	\N	\N	\N	\N
12	STU-2026-0012	Pedro	Gonzalez	male	123 Sample Street, City	active	2026-08-08 22:33:45	2026-08-13 11:21:49	1234567891566	\N	\N	\N	\N	\N	\N	\N
13	STU-2026-0013	Ana	Reyes	female	123 Sample Street, City	active	2026-08-08 22:33:48	2026-08-13 11:21:50	1234567899365	\N	\N	\N	\N	\N	\N	\N
14	STU-2026-0014	Carlos	Lopez	male	123 Sample Street, City	active	2026-08-08 22:33:50	2026-08-13 11:21:50	1234567892262	\N	\N	\N	\N	\N	\N	\N
15	STU-2026-0015	Juan	Dela Cruz	male	123 Sample Street, City	active	2026-08-08 22:34:42	2026-08-13 11:21:51	1234567894202	\N	\N	\N	\N	\N	\N	\N
16	STU-2026-0016	Maria	Santos	female	123 Sample Street, City	active	2026-08-08 22:34:44	2026-08-13 11:21:52	1234567896944	\N	\N	\N	\N	\N	\N	\N
17	STU-2026-0017	Pedro	Gonzalez	male	123 Sample Street, City	active	2026-08-08 22:34:46	2026-08-13 11:21:52	1234567896013	\N	\N	\N	\N	\N	\N	\N
18	STU-2026-0018	Ana	Reyes	female	123 Sample Street, City	active	2026-08-08 22:34:48	2026-08-13 11:21:53	1234567893944	\N	\N	\N	\N	\N	\N	\N
19	STU-2026-0019	Carlos	Lopez	male	123 Sample Street, City	active	2026-08-08 22:34:50	2026-08-13 11:21:53	1234567897632	\N	\N	\N	\N	\N	\N	\N
20	STU-2026-0020	Juan	Dela Cruz	male	123 Sample Street, City	active	2026-08-08 22:35:27	2026-08-13 11:21:54	1234567893419	\N	\N	\N	\N	\N	\N	\N
21	STU-2026-0021	Maria	Santos	female	123 Sample Street, City	active	2026-08-08 22:35:29	2026-08-13 11:21:54	1234567898645	\N	\N	\N	\N	\N	\N	\N
22	STU-2026-0022	Pedro	Gonzalez	male	123 Sample Street, City	active	2026-08-08 22:35:31	2026-08-13 11:21:55	1234567894473	\N	\N	\N	\N	\N	\N	\N
23	STU-2026-0023	Ana	Reyes	female	123 Sample Street, City	active	2026-08-08 22:35:33	2026-08-13 11:21:56	1234567898866	\N	\N	\N	\N	\N	\N	\N
24	STU-2026-0024	Carlos	Lopez	male	123 Sample Street, City	active	2026-08-08 22:35:35	2026-08-13 11:21:56	1234567897420	\N	\N	\N	\N	\N	\N	\N
25	STU-2026-0025	Juan	Dela Cruz	male	123 Sample Street, City	active	2026-08-08 22:36:07	2026-08-13 11:21:57	1234567892112	\N	\N	\N	\N	\N	\N	\N
26	STU-2026-0026	Maria	Santos	female	123 Sample Street, City	active	2026-08-08 22:36:10	2026-08-13 11:21:57	1234567895939	\N	\N	\N	\N	\N	\N	\N
27	STU-2026-0027	Pedro	Gonzalez	male	123 Sample Street, City	active	2026-08-08 22:36:11	2026-08-13 11:21:58	1234567896318	\N	\N	\N	\N	\N	\N	\N
28	STU-2026-0028	Ana	Reyes	female	123 Sample Street, City	active	2026-08-08 22:36:14	2026-08-13 11:21:58	1234567893909	\N	\N	\N	\N	\N	\N	\N
29	STU-2026-0029	Carlos	Lopez	male	123 Sample Street, City	active	2026-08-08 22:36:16	2026-08-13 11:21:59	1234567897020	\N	\N	\N	\N	\N	\N	\N
30	STU-2026-0030	Juan	Dela Cruz	male	123 Sample Street, City	active	2026-08-08 22:37:08	2026-08-13 11:21:59	1234567894919	\N	\N	\N	\N	\N	\N	\N
31	STU-2026-0031	Maria	Santos	female	123 Sample Street, City	active	2026-08-08 22:37:10	2026-08-13 11:22:00	1234567894935	\N	\N	\N	\N	\N	\N	\N
32	STU-2026-0032	Pedro	Gonzalez	male	123 Sample Street, City	active	2026-08-08 22:37:12	2026-08-13 11:22:00	1234567891573	\N	\N	\N	\N	\N	\N	\N
34	STU-2026-0034	CarlosEdited2	Lopez	male	123 Sample Street, City	active	2026-08-08 22:37:16	2026-08-13 11:22:01	1234567897554	\N	\N	\N	\N	\N	\N	\N
56	STU-2026-0056	Roseanne	Park	female	Pasay City Manila, Philippines	active	2026-08-14 13:11:39	2026-08-14 13:11:39	104758104376	\N	\N	\N	\N	\N	\N	\N
57	STU-2026-0057	Bobby	Dela Merced	male	Sitio Uno, Pulong Palazan, Candaba, Pampanga	active	2026-08-17 21:34:29	2026-08-17 21:34:29	103944023393	\N	\N	13	\N	filipino	NA	INC
58	STU-2026-0058	Troy	Escapo	male	Sitio Uno, Pulong Palazan, Candaba, Pampanga	active	2026-08-17 21:34:56	2026-08-17 21:34:56	103944023395	\N	\N	15	\N	filipino	NA	INC
59	STU-2026-0059	Rachel	Gretta	male	Sitio Uno, Pulong Palazan, Candaba, Pampanga	active	2026-08-17 21:35:12	2026-08-17 21:35:12	103944023396	\N	\N	16	\N	filipino	NA	INC
60	STU-2026-0060	Sky	Morano	male	Sitio Uno, Pulong Palazan, Candaba, Pampanga	active	2026-08-17 21:35:35	2026-08-17 21:35:35	103944023397	\N	\N	17	\N	filipino	NA	INC
61	STU-2026-0061	Terron	Trump	female	Sitio Uno, Pulong Palazan, Candaba, Pampanga	active	2026-08-17 21:35:54	2026-08-17 21:35:54	103944023398	\N	\N	18	\N	filipino	NA	INC
62	STU-2026-0062	Marga	Santiago	female	Sitio Uno, Pulong Palazan, Candaba, Pampanga	active	2026-08-17 21:36:10	2026-08-17 21:36:10	103944023399	\N	\N	19	\N	filipino	NA	INC
63	STU-2026-0063	Marganx	Santos	female	Sitio Uno, Pulong Palazan, Candaba, Pampanga	active	2026-08-17 21:36:23	2026-08-17 21:36:23	103944023400	\N	\N	20	\N	filipino	NA	INC
64	STU-2026-0064	Tonee	Sy	female	Sitio Uno, Pulong Palazan, Candaba, Pampanga	active	2026-08-17 21:36:36	2026-08-17 21:36:36	103944023401	\N	\N	21	\N	filipino	NA	INC
65	STU-2026-0065	Christaina	Xy	female	Sitio Uno, Pulong Palazan, Candaba, Pampanga	active	2026-08-17 21:36:46	2026-08-17 21:36:46	103944023402	\N	\N	22	\N	filipino	NA	INC
\.


--
-- Data for Name: enrollments; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.enrollments (id, student_id, grade_level, status, created_at, updated_at, level, school_year_id, section_id) FROM stdin;
1	5	10	active	2026-08-08 22:33:10	2026-08-08 22:33:10	hs	8	3
2	6	10	active	2026-08-08 22:33:12	2026-08-08 22:33:12	hs	8	3
3	7	10	active	2026-08-08 22:33:14	2026-08-08 22:33:14	hs	8	3
4	8	10	active	2026-08-08 22:33:16	2026-08-08 22:33:16	hs	8	3
5	9	10	active	2026-08-08 22:33:18	2026-08-08 22:33:18	hs	8	3
6	10	10	active	2026-08-08 22:33:42	2026-08-08 22:33:42	hs	8	3
7	11	10	active	2026-08-08 22:33:44	2026-08-08 22:33:44	hs	8	3
8	12	10	active	2026-08-08 22:33:47	2026-08-08 22:33:47	hs	8	3
9	13	10	active	2026-08-08 22:33:49	2026-08-08 22:33:49	hs	8	3
10	14	10	active	2026-08-08 22:33:51	2026-08-08 22:33:51	hs	8	3
11	15	10	active	2026-08-08 22:34:43	2026-08-08 22:34:43	hs	8	3
12	16	10	active	2026-08-08 22:34:45	2026-08-08 22:34:45	hs	8	3
13	17	10	active	2026-08-08 22:34:47	2026-08-08 22:34:47	hs	8	3
14	18	10	active	2026-08-08 22:34:49	2026-08-08 22:34:49	hs	8	3
15	19	10	active	2026-08-08 22:34:51	2026-08-08 22:34:51	hs	8	3
16	20	10	active	2026-08-08 22:35:28	2026-08-08 22:35:28	hs	8	3
17	21	10	active	2026-08-08 22:35:31	2026-08-08 22:35:31	hs	8	3
18	22	10	active	2026-08-08 22:35:32	2026-08-08 22:35:32	hs	8	3
19	23	10	active	2026-08-08 22:35:34	2026-08-08 22:35:34	hs	8	3
20	24	10	active	2026-08-08 22:35:36	2026-08-08 22:35:36	hs	8	3
21	25	10	active	2026-08-08 22:36:08	2026-08-08 22:36:08	hs	8	3
22	26	10	active	2026-08-08 22:36:10	2026-08-08 22:36:10	hs	8	3
23	27	10	active	2026-08-08 22:36:13	2026-08-08 22:36:13	hs	8	3
24	28	10	active	2026-08-08 22:36:15	2026-08-08 22:36:15	hs	8	3
25	29	10	active	2026-08-08 22:36:17	2026-08-08 22:36:17	hs	8	3
26	30	10	active	2026-08-08 22:37:09	2026-08-08 22:37:09	hs	8	3
27	31	10	active	2026-08-08 22:37:11	2026-08-08 22:37:11	hs	8	3
28	32	10	active	2026-08-08 22:37:13	2026-08-08 22:37:13	hs	8	3
30	34	10	active	2026-08-08 22:37:17	2026-08-08 22:37:17	hs	8	3
32	40	1	active	2026-08-12 11:30:54	2026-08-12 11:30:54	\N	7	5
33	41	10	active	2026-08-12 16:52:23	2026-08-12 16:52:23	\N	7	3
31	2	1	active	2026-08-11 21:00:05	2026-08-12 21:27:15	\N	7	5
34	11	7	active	2026-08-13 10:20:42	2026-08-13 10:20:42	hs	\N	4
35	12	7	active	2026-08-13 10:21:14	2026-08-13 10:21:14	hs	\N	4
36	13	7	active	2026-08-13 10:21:41	2026-08-13 10:21:41	hs	\N	4
37	14	7	active	2026-08-13 10:22:20	2026-08-13 10:22:20	hs	\N	4
38	15	7	active	2026-08-13 10:23:39	2026-08-13 10:23:39	hs	\N	4
39	16	7	active	2026-08-13 10:24:29	2026-08-13 10:24:29	hs	\N	4
40	17	7	active	2026-08-13 10:25:18	2026-08-13 10:25:18	hs	\N	4
41	18	7	active	2026-08-13 10:25:48	2026-08-13 10:25:48	hs	\N	4
42	19	7	active	2026-08-13 10:26:20	2026-08-13 10:26:20	hs	\N	4
43	20	7	active	2026-08-13 10:26:43	2026-08-13 10:26:43	hs	\N	4
44	55	8	active	2026-08-14 13:08:26	2026-08-14 13:08:26	\N	7	6
45	56	8	active	2026-08-14 13:11:47	2026-08-14 13:11:47	\N	7	6
\.


--
-- Data for Name: attendance_logs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.attendance_logs (id, enrollment_id, scan_type, session_type, scan_time, scanned_by_user_id, device_id, created_at, updated_at) FROM stdin;
1	1	IN	morning	2026-08-10 07:15:00	\N	TEST-DISPLAY-VERIFICATION	2026-08-10 10:52:23	2026-08-10 10:52:23
2	32	IN	whole_day	2026-08-12 11:37:24	\N	\N	2026-08-12 11:37:32	2026-08-12 11:37:32
3	32	IN	whole_day	2026-08-12 11:46:55	\N	\N	2026-08-12 11:47:04	2026-08-12 11:47:04
4	32	IN	whole_day	2026-08-12 11:47:15	\N	\N	2026-08-12 11:47:23	2026-08-12 11:47:23
5	32	IN	whole_day	2026-08-12 11:47:27	\N	\N	2026-08-12 11:47:35	2026-08-12 11:47:35
6	32	IN	whole_day	2026-08-12 11:47:42	\N	\N	2026-08-12 11:47:51	2026-08-12 11:47:51
7	32	IN	whole_day	2026-08-12 13:58:16	\N	\N	2026-08-12 13:58:22	2026-08-12 13:58:22
8	32	OUT	whole_day	2026-08-12 15:27:02	\N	\N	2026-08-12 15:27:07	2026-08-12 15:27:07
9	32	IN	whole_day	2026-08-12 19:28:27	\N	\N	2026-08-12 19:28:48	2026-08-12 19:28:48
10	31	IN	whole_day	2026-08-12 21:29:12	\N	\N	2026-08-12 21:29:19	2026-08-12 21:29:19
11	31	IN	whole_day	2026-08-12 21:48:17	\N	\N	2026-08-12 21:48:21	2026-08-12 21:48:21
12	31	IN	whole_day	2026-08-12 21:55:51	\N	\N	2026-08-12 21:55:54	2026-08-12 21:55:54
13	31	IN	whole_day	2026-08-12 22:12:12	\N	\N	2026-08-12 22:12:13	2026-08-12 22:12:13
14	31	IN	whole_day	2026-08-12 22:31:28	\N	\N	2026-08-12 22:31:31	2026-08-12 22:31:31
15	31	IN	whole_day	2026-08-12 22:41:44	\N	\N	2026-08-12 22:41:47	2026-08-12 22:41:47
17	34	IN	morning	2026-08-13 08:30:00	\N	TEST-DISPLAY-VERIFICATION	2026-08-13 10:29:17	2026-08-13 10:29:17
18	35	IN	morning	2026-08-13 08:45:00	\N	TEST-DISPLAY-VERIFICATION	2026-08-13 10:29:44	2026-08-13 10:29:44
19	36	IN	morning	2026-08-13 09:00:00	\N	TEST-DISPLAY-VERIFICATION	2026-08-13 10:29:59	2026-08-13 10:29:59
20	32	IN	whole_day	2026-08-13 13:51:03	\N	\N	2026-08-13 13:51:12	2026-08-13 13:51:12
21	32	IN	whole_day	2026-08-13 13:59:25	\N	\N	2026-08-13 13:59:30	2026-08-13 13:59:30
22	32	IN	whole_day	2026-08-13 14:06:53	\N	\N	2026-08-13 14:06:57	2026-08-13 14:06:57
24	32	IN	whole_day	2026-08-13 15:11:14	\N	\N	2026-08-13 15:11:19	2026-08-13 15:11:19
25	31	IN	whole_day	2026-08-13 15:56:28	\N	\N	2026-08-13 15:56:31	2026-08-13 15:56:31
26	31	IN	whole_day	2026-08-13 16:29:43	\N	\N	2026-08-13 16:29:45	2026-08-13 16:29:45
27	33	IN	afternoon	2026-08-15 13:29:53	\N	\N	2026-08-15 13:30:11	2026-08-15 13:30:11
28	33	IN	afternoon	2026-08-15 13:34:49	\N	\N	2026-08-15 13:34:54	2026-08-15 13:34:54
\.


--
-- Data for Name: attendance_verifications; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.attendance_verifications (id, attendance_log_id, teacher_id, status, remarks, verified_at, created_at, updated_at, enrollment_id, teaching_assignment_id, attendance_date, resolved_by) FROM stdin;
1	\N	6	excused	\N	2026-08-13 11:05:53	2026-08-13 11:05:53	2026-08-13 11:05:53	37	2	2026-08-13	teacher
2	\N	6	excused	\N	2026-08-13 11:05:53	2026-08-13 11:05:53	2026-08-13 11:05:53	37	2	2026-08-13	teacher
3	\N	6	excused	sick	2026-08-15 22:13:57	2026-08-15 22:13:57	2026-08-15 22:13:57	1	1	2026-08-15	teacher
\.


--
-- Data for Name: attendance_verification_histories; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.attendance_verification_histories (id, attendance_verification_id, previous_status, new_status, changed_by, remarks, created_at) FROM stdin;
\.


--
-- Data for Name: bulk_imports; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.bulk_imports (id, original_filename, file_path, file_hash, status, total_rows, valid_count, error_count, warning_count, success_count, failed_count, created_by, created_at, updated_at) FROM stdin;
1	School-Forms-1.xlsx	imports/3f587c8d-8e71-475b-ab79-de04bb925e49.xlsx	0783524c29a65ded11400744e250304c68933f3cb3c2c0759b43da27751710bb	pending	0	0	0	0	0	0	15	2026-08-16 10:39:41	2026-08-16 10:39:41
2	grade7 courage.xlsx	imports/c29ed69f-099e-4d29-a311-f3ce3b6b0ece.xlsx	3ef5ff016c07e546f451772d725ef7c92ba0ca9409abfd8febfece574edf3b82	validated	10	10	0	10	0	0	15	2026-08-17 21:28:33	2026-08-17 21:28:41
3	grade7 courage.xlsx	imports/4515380d-c31f-45e9-84c0-8fb9c27fad7c.xlsx	3ef5ff016c07e546f451772d725ef7c92ba0ca9409abfd8febfece574edf3b82	validated	10	10	0	0	0	0	15	2026-08-17 21:31:18	2026-08-17 21:31:27
4	grade7 courage.xlsx	imports/97a25946-4b22-41e9-ba55-64791f8b7825.xlsx	3ef5ff016c07e546f451772d725ef7c92ba0ca9409abfd8febfece574edf3b82	completed_with_issues	10	10	0	0	9	10	15	2026-08-17 21:34:01	2026-08-17 21:37:00
\.


--
-- Data for Name: bulk_import_issues; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.bulk_import_issues (id, bulk_import_id, row_number, issue_type, severity, field, message, raw_data, status, resolved_at, created_at, updated_at) FROM stdin;
2	2	10	section_not_found	warning	sectionName	Section "Courage" not found or inactive for highschool grade 7.	{"LRN":"103944023393","Learner Name":"Dela Merced, Bobby","Sex":"male","Age":"13","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Dela Merced, Max","Mother's Maiden Name":"Dela Merced, Mary","Guardian Name":"Dela Merced, Mary","Guardian Relationship":"guardian","Guardian Contact":"09089039028","Guardian Email":"j.delacruz@gmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	acknowledged	2026-08-17 21:30:55	2026-08-17 21:28:39	2026-08-17 21:30:55
3	2	11	section_not_found	warning	sectionName	Section "Courage" not found or inactive for highschool grade 7.	{"LRN":"103944023394","Learner Name":"De Laila, Mawi","Sex":"male","Age":"14","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"De Laila, Steph","Mother's Maiden Name":"De Laila, Stephanie","Guardian Name":"De Laila, Stephanie","Guardian Relationship":"guardian","Guardian Contact":"09089039029","Guardian Email":"mariaclara.santos@yahoo.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	acknowledged	2026-08-17 21:30:55	2026-08-17 21:28:39	2026-08-17 21:30:55
4	2	12	section_not_found	warning	sectionName	Section "Courage" not found or inactive for highschool grade 7.	{"LRN":"103944023395","Learner Name":"Escapo, Troy","Sex":"male","Age":"15","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Escapo, Tracy","Mother's Maiden Name":"Escapo, Pat","Guardian Name":"Escapo, Pat","Guardian Relationship":"guardian","Guardian Contact":"09089039030","Guardian Email":"ethan.reyes@outlook.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	acknowledged	2026-08-17 21:30:55	2026-08-17 21:28:39	2026-08-17 21:30:55
5	2	13	section_not_found	warning	sectionName	Section "Courage" not found or inactive for highschool grade 7.	{"LRN":"103944023396","Learner Name":"Gretta, Rachel","Sex":"male","Age":"16","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Gretta, Yecs","Mother's Maiden Name":"Gretta, Missy","Guardian Name":"Gretta, Missy","Guardian Relationship":"guardian","Guardian Contact":"09089039031","Guardian Email":"sofia.garcia@gmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	acknowledged	2026-08-17 21:30:55	2026-08-17 21:28:39	2026-08-17 21:30:55
6	2	14	section_not_found	warning	sectionName	Section "Courage" not found or inactive for highschool grade 7.	{"LRN":"103944023397","Learner Name":"Morano, Sky","Sex":"male","Age":"17","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Morano, Wise","Mother's Maiden Name":"Morano, Mika","Guardian Name":"Morano, Mika","Guardian Relationship":"guardian","Guardian Contact":"09089039032","Guardian Email":"lucas.dizon@hotmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	acknowledged	2026-08-17 21:30:55	2026-08-17 21:28:39	2026-08-17 21:30:55
7	2	15	section_not_found	warning	sectionName	Section "Courage" not found or inactive for highschool grade 7.	{"LRN":"103944023398","Learner Name":"Trump, Terron","Sex":"female","Age":"18","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Trump, Magnus","Mother's Maiden Name":"Trump, Laine","Guardian Name":"Trump, Laine","Guardian Relationship":"guardian","Guardian Contact":"09089039033","Guardian Email":"samantha.cruz@gmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	acknowledged	2026-08-17 21:30:55	2026-08-17 21:28:39	2026-08-17 21:30:55
8	2	16	section_not_found	warning	sectionName	Section "Courage" not found or inactive for highschool grade 7.	{"LRN":"103944023399","Learner Name":"Santiago, Marga","Sex":"female","Age":"19","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Santos, Edward","Mother's Maiden Name":"Santos, Gaile","Guardian Name":"Santos, Gaile","Guardian Relationship":"guardian","Guardian Contact":"09089039034","Guardian Email":"gabriel.ramos@yahoo.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	acknowledged	2026-08-17 21:30:55	2026-08-17 21:28:39	2026-08-17 21:30:55
9	2	17	section_not_found	warning	sectionName	Section "Courage" not found or inactive for highschool grade 7.	{"LRN":"103944023400","Learner Name":"Santos, Marganx","Sex":"female","Age":"20","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Santiago, Locar","Mother's Maiden Name":"Santiago, Angel","Guardian Name":"Santiago, Angel","Guardian Relationship":"guardian","Guardian Contact":"09089039035","Guardian Email":"andrea.torres@outlook.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	acknowledged	2026-08-17 21:30:55	2026-08-17 21:28:39	2026-08-17 21:30:55
10	2	18	section_not_found	warning	sectionName	Section "Courage" not found or inactive for highschool grade 7.	{"LRN":"103944023401","Learner Name":"Sy, Tonee","Sex":"female","Age":"21","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Sy, Anthone","Mother's Maiden Name":"Sy, Ash","Guardian Name":"Sy, Ash","Guardian Relationship":"guardian","Guardian Contact":"09089039036","Guardian Email":"joshua.bonifacio@gmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	acknowledged	2026-08-17 21:30:55	2026-08-17 21:28:39	2026-08-17 21:30:55
11	2	19	section_not_found	warning	sectionName	Section "Courage" not found or inactive for highschool grade 7.	{"LRN":"103944023402","Learner Name":"Xy, Christaina","Sex":"female","Age":"22","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Xy, Lloyd","Mother's Maiden Name":"Xy, Thea","Guardian Name":"Xy, Thea","Guardian Relationship":"guardian","Guardian Contact":"09089039037","Guardian Email":"chloe.luna@gmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	acknowledged	2026-08-17 21:30:55	2026-08-17 21:28:39	2026-08-17 21:30:55
12	4	10	system_error	error	\N	An unexpected system error occurred while processing this row. Please contact the system administrator.	{"LRN":"103944023393","Learner Name":"Dela Merced, Bobby","Sex":"male","Age":"13","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Dela Merced, Max","Mother's Maiden Name":"Dela Merced, Mary","Guardian Name":"Dela Merced, Mary","Guardian Relationship":"guardian","Guardian Contact":"09089039028","Guardian Email":"j.delacruz@gmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	unresolved	\N	2026-08-17 21:34:48	2026-08-17 21:34:48
13	4	11	system_error	error	\N	An unexpected system error occurred while processing this row. Please contact the system administrator.	{"LRN":"103944023394","Learner Name":"De Laila, Mawi","Sex":"male","Age":"14","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"De Laila, Steph","Mother's Maiden Name":"De Laila, Stephanie","Guardian Name":"De Laila, Stephanie","Guardian Relationship":"guardian","Guardian Contact":"09089039029","Guardian Email":"mariaclara.santos@yahoo.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	unresolved	\N	2026-08-17 21:34:53	2026-08-17 21:34:53
14	4	12	system_error	error	\N	An unexpected system error occurred while processing this row. Please contact the system administrator.	{"LRN":"103944023395","Learner Name":"Escapo, Troy","Sex":"male","Age":"15","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Escapo, Tracy","Mother's Maiden Name":"Escapo, Pat","Guardian Name":"Escapo, Pat","Guardian Relationship":"guardian","Guardian Contact":"09089039030","Guardian Email":"ethan.reyes@outlook.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	unresolved	\N	2026-08-17 21:35:09	2026-08-17 21:35:09
15	4	13	system_error	error	\N	An unexpected system error occurred while processing this row. Please contact the system administrator.	{"LRN":"103944023396","Learner Name":"Gretta, Rachel","Sex":"male","Age":"16","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Gretta, Yecs","Mother's Maiden Name":"Gretta, Missy","Guardian Name":"Gretta, Missy","Guardian Relationship":"guardian","Guardian Contact":"09089039031","Guardian Email":"sofia.garcia@gmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	unresolved	\N	2026-08-17 21:35:32	2026-08-17 21:35:32
16	4	14	system_error	error	\N	An unexpected system error occurred while processing this row. Please contact the system administrator.	{"LRN":"103944023397","Learner Name":"Morano, Sky","Sex":"male","Age":"17","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Morano, Wise","Mother's Maiden Name":"Morano, Mika","Guardian Name":"Morano, Mika","Guardian Relationship":"guardian","Guardian Contact":"09089039032","Guardian Email":"lucas.dizon@hotmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	unresolved	\N	2026-08-17 21:35:53	2026-08-17 21:35:53
17	4	15	system_error	error	\N	An unexpected system error occurred while processing this row. Please contact the system administrator.	{"LRN":"103944023398","Learner Name":"Trump, Terron","Sex":"female","Age":"18","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Trump, Magnus","Mother's Maiden Name":"Trump, Laine","Guardian Name":"Trump, Laine","Guardian Relationship":"guardian","Guardian Contact":"09089039033","Guardian Email":"samantha.cruz@gmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	unresolved	\N	2026-08-17 21:36:07	2026-08-17 21:36:07
18	4	16	system_error	error	\N	An unexpected system error occurred while processing this row. Please contact the system administrator.	{"LRN":"103944023399","Learner Name":"Santiago, Marga","Sex":"female","Age":"19","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Santos, Edward","Mother's Maiden Name":"Santos, Gaile","Guardian Name":"Santos, Gaile","Guardian Relationship":"guardian","Guardian Contact":"09089039034","Guardian Email":"gabriel.ramos@yahoo.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	unresolved	\N	2026-08-17 21:36:21	2026-08-17 21:36:21
19	4	17	system_error	error	\N	An unexpected system error occurred while processing this row. Please contact the system administrator.	{"LRN":"103944023400","Learner Name":"Santos, Marganx","Sex":"female","Age":"20","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Santiago, Locar","Mother's Maiden Name":"Santiago, Angel","Guardian Name":"Santiago, Angel","Guardian Relationship":"guardian","Guardian Contact":"09089039035","Guardian Email":"andrea.torres@outlook.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	unresolved	\N	2026-08-17 21:36:33	2026-08-17 21:36:33
20	4	18	system_error	error	\N	An unexpected system error occurred while processing this row. Please contact the system administrator.	{"LRN":"103944023401","Learner Name":"Sy, Tonee","Sex":"female","Age":"21","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Sy, Anthone","Mother's Maiden Name":"Sy, Ash","Guardian Name":"Sy, Ash","Guardian Relationship":"guardian","Guardian Contact":"09089039036","Guardian Email":"joshua.bonifacio@gmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	unresolved	\N	2026-08-17 21:36:45	2026-08-17 21:36:45
21	4	19	system_error	error	\N	An unexpected system error occurred while processing this row. Please contact the system administrator.	{"LRN":"103944023402","Learner Name":"Xy, Christaina","Sex":"female","Age":"22","Birthplace":null,"Mother Tongue":"filipino","IP\\/Ethnic Group":"NA","Religion":"INC","Complete Address":"Sitio Uno, Pulong Palazan, Candaba, Pampanga","Father's Name":"Xy, Lloyd","Mother's Maiden Name":"Xy, Thea","Guardian Name":"Xy, Thea","Guardian Relationship":"guardian","Guardian Contact":"09089039037","Guardian Email":"chloe.luna@gmail.com","Department Level":"highschool","Grade Level":"7","Section":"Courage"}	unresolved	\N	2026-08-17 21:36:57	2026-08-17 21:36:57
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: email_logs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.email_logs (id, attendance_log_id, student_id, email, scan_type, status, attempt_count, last_attempt_at, sent_at, created_at, updated_at) FROM stdin;
1	2	40	arriane.estrada.dev@gmail.com	IN	sent	1	2026-08-12 11:37:24	2026-08-12 11:37:24	2026-08-12 11:37:36	2026-08-12 11:37:42
2	8	40	arriane.estrada.dev@gmail.com	OUT	sent	1	2026-08-12 15:27:02	2026-08-12 15:27:02	2026-08-12 15:27:08	2026-08-12 15:27:12
3	10	2	arriane.estrada.dev@gmail.com	IN	sent	1	2026-08-12 21:29:39	2026-08-12 21:29:39	2026-08-12 21:29:23	2026-08-12 21:29:39
4	20	40	arriane.estrada.dev@gmail.com	IN	sent	1	2026-08-13 13:51:36	2026-08-13 13:51:36	2026-08-13 13:51:21	2026-08-13 13:51:36
5	25	2	arriane.estrada.dev@gmail.com	IN	sent	1	2026-08-13 15:56:42	2026-08-13 15:56:42	2026-08-13 15:56:34	2026-08-13 15:56:42
6	27	41	arriane.estrada0@gmail.com	IN	sent	1	2026-08-15 13:32:32	2026-08-15 13:32:32	2026-08-15 13:30:37	2026-08-15 13:32:32
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: flagged_scans; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.flagged_scans (id, attendance_log_id, flag_type, description, created_at, updated_at) FROM stdin;
1	2	late_arrival	Student arrived late	2026-08-12 11:37:35	2026-08-12 11:37:35
2	3	duplicate_scan	Duplicate IN scan	2026-08-12 11:47:04	2026-08-12 11:47:04
3	4	duplicate_scan	Duplicate IN scan	2026-08-12 11:47:24	2026-08-12 11:47:24
4	5	duplicate_scan	Duplicate IN scan	2026-08-12 11:47:35	2026-08-12 11:47:35
5	6	excess_scan	Exceeded duplicate attempts, cooldown applied	2026-08-12 11:47:51	2026-08-12 11:47:51
6	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 11:48:11	2026-08-12 11:48:11
7	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 11:48:32	2026-08-12 11:48:32
8	7	excess_scan	Exceeded duplicate attempts, cooldown applied	2026-08-12 13:58:23	2026-08-12 13:58:23
9	9	duplicate_scan	Duplicate IN scan rejected	2026-08-12 19:28:49	2026-08-12 19:28:49
10	10	late_arrival	Student arrived late	2026-08-12 21:29:21	2026-08-12 21:29:21
11	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 21:30:07	2026-08-12 21:30:07
12	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 21:30:27	2026-08-12 21:30:27
13	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 21:30:50	2026-08-12 21:30:50
14	11	excess_scan	Exceeded duplicate attempts, cooldown applied	2026-08-12 21:48:23	2026-08-12 21:48:23
15	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 21:49:21	2026-08-12 21:49:21
16	12	excess_scan	Exceeded duplicate attempts, cooldown applied	2026-08-12 21:55:55	2026-08-12 21:55:55
17	13	excess_scan	Exceeded duplicate attempts, cooldown applied	2026-08-12 22:12:14	2026-08-12 22:12:14
18	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 22:12:22	2026-08-12 22:12:22
19	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 22:12:29	2026-08-12 22:12:29
20	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 22:12:39	2026-08-12 22:12:39
21	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 22:12:47	2026-08-12 22:12:47
22	14	excess_scan	Exceeded duplicate attempts, cooldown applied	2026-08-12 22:31:32	2026-08-12 22:31:32
23	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 22:31:42	2026-08-12 22:31:42
24	15	excess_scan	Exceeded duplicate attempts, cooldown applied	2026-08-12 22:41:48	2026-08-12 22:41:48
25	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 22:41:58	2026-08-12 22:41:58
26	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 22:42:09	2026-08-12 22:42:09
27	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 22:42:46	2026-08-12 22:42:46
28	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 22:43:19	2026-08-12 22:43:19
29	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-12 22:43:32	2026-08-12 22:43:32
30	20	late_arrival	Student arrived late	2026-08-13 13:51:20	2026-08-13 13:51:20
31	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-13 13:53:01	2026-08-13 13:53:01
32	21	duplicate_scan	Duplicate IN scan	2026-08-13 13:59:30	2026-08-13 13:59:30
33	22	duplicate_scan	Duplicate IN scan	2026-08-13 14:06:58	2026-08-13 14:06:58
34	24	excess_scan	Exceeded duplicate attempts, cooldown applied	2026-08-13 15:11:20	2026-08-13 15:11:20
35	25	late_arrival	Student arrived late	2026-08-13 15:56:33	2026-08-13 15:56:33
36	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-13 15:57:05	2026-08-13 15:57:05
37	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-13 15:57:15	2026-08-13 15:57:15
38	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-13 15:57:25	2026-08-13 15:57:25
39	\N	excess_scan	Scan blocked by cooldown (spam shield)	2026-08-13 15:57:43	2026-08-13 15:57:43
40	26	excess_scan	Exceeded duplicate attempts, cooldown applied	2026-08-13 16:29:46	2026-08-13 16:29:46
41	28	duplicate_scan	Duplicate IN scan	2026-08-15 13:35:01	2026-08-15 13:35:01
\.


--
-- Data for Name: grading_configs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.grading_configs (id, teaching_assignment_id, assessment_category_id, weight, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: guardians; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.guardians (id, student_id, name, relationship, email, created_at, updated_at, contact_number) FROM stdin;
1	2	Arriane Rueda Estrada	mother	arriane.estrada.dev@gmail.com	2026-08-08 12:36:46	2026-08-08 12:36:46	\N
2	35	Arriane Rueda Estrada	mother	arriane.estrada.dev@gmail.com	2026-08-10 00:02:07	2026-08-10 00:02:07	\N
3	36	Guardian Name	mother	g1786293794717@example.com	2026-08-10 00:43:17	2026-08-10 00:43:17	\N
4	38	Guard S	father	sflow1786294223594@example.com	2026-08-10 00:50:27	2026-08-10 00:50:27	\N
5	34	Guardian Carlos	father	g1786294633608@example.com	2026-08-10 00:57:17	2026-08-10 00:57:17	\N
6	39	dfsdfdsfdsf	mother	aes@gmail.com	2026-08-10 01:17:08	2026-08-10 01:17:08	\N
7	40	Arriane Rueda Estrada	guardian	arriane.estrada.dev@gmail.com	2026-08-12 11:29:56	2026-08-12 11:29:56	\N
8	41	Johnmar Villaluna	guardian	arriane.estrada0@gmail.com	2026-08-12 16:52:15	2026-08-12 16:52:15	09078947034
9	55	Jennie Kim	mother	jenniekim93@gmail.com	2026-08-14 13:08:19	2026-08-14 13:08:19	\N
10	56	Kim Jisoo	mother	kimjisoo92@gmail.com	2026-08-14 13:11:40	2026-08-14 13:11:40	\N
11	57	Dela Merced, Mary	guardian	j.delacruz@gmail.com	2026-08-17 21:34:32	2026-08-17 21:34:32	09089039028
13	58	Escapo, Pat	guardian	ethan.reyes@outlook.com	2026-08-17 21:34:58	2026-08-17 21:34:58	09089039030
15	59	Gretta, Missy	guardian	sofia.garcia@gmail.com	2026-08-17 21:35:15	2026-08-17 21:35:15	09089039031
17	60	Morano, Mika	guardian	lucas.dizon@hotmail.com	2026-08-17 21:35:37	2026-08-17 21:35:37	09089039032
19	61	Trump, Laine	guardian	samantha.cruz@gmail.com	2026-08-17 21:35:57	2026-08-17 21:35:57	09089039033
21	62	Santos, Gaile	guardian	gabriel.ramos@yahoo.com	2026-08-17 21:36:11	2026-08-17 21:36:11	09089039034
23	63	Santiago, Angel	guardian	andrea.torres@outlook.com	2026-08-17 21:36:25	2026-08-17 21:36:25	09089039035
25	64	Sy, Ash	guardian	joshua.bonifacio@gmail.com	2026-08-17 21:36:37	2026-08-17 21:36:37	09089039036
27	65	Xy, Thea	guardian	chloe.luna@gmail.com	2026-08-17 21:36:48	2026-08-17 21:36:48	09089039037
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: login_logs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.login_logs (id, user_id, email_attempted, status, ip_address, user_agent, attempted_at, created_at) FROM stdin;
1	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 16:08:15	2026-08-09 16:08:15
2	\N	arriane.estrada@example.comPassword123	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-09 18:39:39	2026-08-09 18:39:39
3	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-09 18:40:38	2026-08-09 18:40:38
4	16	arriane.estrada.dev@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 18:43:19	2026-08-09 18:43:19
5	51	test.teacher.unique@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-09 18:46:39	2026-08-09 18:46:39
6	58	test@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-09 18:49:10	2026-08-09 18:49:10
7	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-09 18:55:26	2026-08-09 18:55:26
8	59	arriane.estrada.new@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-09 18:57:15	2026-08-09 18:57:15
9	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 20:20:22	2026-08-09 20:20:22
10	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 22:48:56	2026-08-09 22:48:56
11	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 22:49:00	2026-08-09 22:49:00
12	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 22:49:00	2026-08-09 22:49:00
13	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 22:49:05	2026-08-09 22:49:05
14	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 22:49:06	2026-08-09 22:49:06
15	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 22:49:06	2026-08-09 22:49:06
16	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 22:49:07	2026-08-09 22:49:07
17	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 22:51:07	2026-08-09 22:51:07
18	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 22:51:55	2026-08-09 22:51:55
19	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 22:57:05	2026-08-09 22:57:05
20	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 22:59:44	2026-08-09 22:59:44
21	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 23:11:06	2026-08-09 23:11:06
22	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 23:19:11	2026-08-09 23:19:11
23	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 23:55:11	2026-08-09 23:55:11
24	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-09 23:57:28	2026-08-09 23:57:28
25	\N	superadmin@cis.educ.ph	failed	::1	Mozilla/5.0 (Linux; Android 13; ELN-W09 Build/HONORELN-W09; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Safari/537.36 [FB_IAB/FB4A;FBAV/573.0.0.44.88;]	2026-08-10 00:14:19	2026-08-10 00:14:19
26	\N	superadmin@cis.educ.ph	failed	::1	Mozilla/5.0 (Linux; Android 13; ELN-W09 Build/HONORELN-W09; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Safari/537.36 [FB_IAB/FB4A;FBAV/573.0.0.44.88;]	2026-08-10 00:14:59	2026-08-10 00:14:59
27	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-10 00:15:53	2026-08-10 00:15:53
28	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-10 00:16:19	2026-08-10 00:16:19
29	16	arriane.estrada.dev@gmail.com	success	::1	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-10 00:19:47	2026-08-10 00:19:47
30	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-10 00:24:07	2026-08-10 00:24:07
31	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.131.0 Chrome/148.0.7778.280 Electron/42.7.0 Safari/537.36	2026-08-10 00:40:42	2026-08-10 00:40:42
32	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-10 01:20:30	2026-08-10 01:20:30
33	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0	2026-08-10 10:09:47	2026-08-10 10:09:47
34	\N	arriane.estrada@example.compassword	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 10:55:27	2026-08-10 10:55:27
35	74	test.teacher.display@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 10:59:09	2026-08-10 10:59:09
36	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 11:18:07	2026-08-10 11:18:07
37	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 11:18:39	2026-08-10 11:18:39
38	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 11:22:15	2026-08-10 11:22:15
39	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 11:24:38	2026-08-10 11:24:38
40	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-10 11:27:14	2026-08-10 11:27:14
41	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 11:32:01	2026-08-10 11:32:01
42	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 11:39:48	2026-08-10 11:39:48
43	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 12:08:50	2026-08-10 12:08:50
44	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 12:19:33	2026-08-10 12:19:33
45	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 12:27:27	2026-08-10 12:27:27
46	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 12:33:17	2026-08-10 12:33:17
47	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 12:39:16	2026-08-10 12:39:16
48	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-10 12:40:59	2026-08-10 12:40:59
49	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-10 12:42:36	2026-08-10 12:42:36
50	\N	arriane.estrada@example.compasswordpassword	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 17:48:11	2026-08-10 17:48:11
51	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 17:58:20	2026-08-10 17:58:20
52	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-10 17:59:07	2026-08-10 17:59:07
53	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-10 17:59:28	2026-08-10 17:59:28
54	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-10 18:00:06	2026-08-10 18:00:06
55	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.0 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-10 18:06:05	2026-08-10 18:06:05
56	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 11:47:27	2026-08-11 11:47:27
57	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 14:38:48	2026-08-11 14:38:48
58	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 14:39:09	2026-08-11 14:39:09
59	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 14:39:27	2026-08-11 14:39:27
60	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 14:40:08	2026-08-11 14:40:08
61	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 18:19:52	2026-08-11 18:19:52
62	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 18:20:08	2026-08-11 18:20:08
63	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 18:20:32	2026-08-11 18:20:32
64	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 18:20:50	2026-08-11 18:20:50
65	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 18:21:08	2026-08-11 18:21:08
66	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 18:58:56	2026-08-11 18:58:56
67	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 19:16:23	2026-08-11 19:16:23
68	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-11 19:31:12	2026-08-11 19:31:12
69	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 11:20:58	2026-08-12 11:20:58
70	\N	ariane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.1 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-12 13:01:05	2026-08-12 13:01:05
108	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-13 16:38:20	2026-08-13 16:38:20
71	\N	ariane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.1 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-12 13:01:30	2026-08-12 13:01:30
72	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 13:02:01	2026-08-12 13:02:01
73	\N	ariane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.1 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-12 13:02:20	2026-08-12 13:02:20
74	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 13:02:28	2026-08-12 13:02:28
75	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 13:03:05	2026-08-12 13:03:05
76	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 13:03:36	2026-08-12 13:03:36
77	\N	arianne.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.132.1 Chrome/148.0.7778.280 Electron/42.7.1 Safari/537.36	2026-08-12 13:10:26	2026-08-12 13:10:26
78	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 14:30:59	2026-08-12 14:30:59
79	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 14:43:03	2026-08-12 14:43:03
80	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 14:55:03	2026-08-12 14:55:03
81	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 15:06:25	2026-08-12 15:06:25
82	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 19:12:47	2026-08-12 19:12:47
83	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 19:13:06	2026-08-12 19:13:06
84	\N	superadmin@cis.edu	failed	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	2026-08-12 19:51:22	2026-08-12 19:51:22
85	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	2026-08-12 19:59:11	2026-08-12 19:59:11
86	54	chanyeolpark92@gmail.com	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 20:32:01	2026-08-12 20:32:01
87	54	chanyeolpark92@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 20:34:00	2026-08-12 20:34:00
88	16	arriane.estrada.dev@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 20:36:42	2026-08-12 20:36:42
89	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 20:37:20	2026-08-12 20:37:20
90	15	superadmin@cis.edu.ph	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 20:39:35	2026-08-12 20:39:35
91	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 20:39:58	2026-08-12 20:39:58
92	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0	2026-08-12 20:42:27	2026-08-12 20:42:27
93	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 20:47:00	2026-08-12 20:47:00
94	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 20:47:36	2026-08-12 20:47:36
95	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 20:48:04	2026-08-12 20:48:04
96	54	chanyeolpark92@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 20:55:15	2026-08-12 20:55:15
97	54	chanyeolpark92@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 20:58:16	2026-08-12 20:58:16
98	56	arriane.estrada@example.com	failed	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 23:01:39	2026-08-12 23:01:39
99	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 23:02:02	2026-08-12 23:02:02
100	54	chanyeolpark92@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-12 23:08:16	2026-08-12 23:08:16
101	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-13 09:34:16	2026-08-13 09:34:16
102	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-13 10:33:17	2026-08-13 10:33:17
103	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-13 10:42:30	2026-08-13 10:42:30
104	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-13 11:09:36	2026-08-13 11:09:36
105	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-13 14:59:59	2026-08-13 14:59:59
106	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-08-13 15:33:33	2026-08-13 15:33:33
107	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-13 16:11:30	2026-08-13 16:11:30
109	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-13 17:05:48	2026-08-13 17:05:48
110	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-13 18:22:56	2026-08-13 18:22:56
111	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-13 18:58:33	2026-08-13 18:58:33
112	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-14 09:07:30	2026-08-14 09:07:30
113	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-14 11:46:03	2026-08-14 11:46:03
114	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-14 12:40:14	2026-08-14 12:40:14
115	56	arriane.estrada@example.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-14 13:16:05	2026-08-14 13:16:05
116	16	arriane.estrada.dev@gmail.com	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-14 13:46:07	2026-08-14 13:46:07
117	16	arriane.estrada.dev@gmail.com	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-14 13:51:39	2026-08-14 13:51:39
118	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-14 16:45:09	2026-08-14 16:45:09
119	16	arriane.estrada.dev@gmail.com	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-14 19:06:37	2026-08-14 19:06:37
120	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-14 19:09:22	2026-08-14 19:09:22
121	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-14 20:27:26	2026-08-14 20:27:26
122	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-15 08:54:33	2026-08-15 08:54:33
123	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-15 10:52:49	2026-08-15 10:52:49
124	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-15 10:55:44	2026-08-15 10:55:44
125	15	superadmin@cis.edu.ph	failed	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-15 12:06:04	2026-08-15 12:06:04
126	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-15 12:06:27	2026-08-15 12:06:27
127	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-15 13:26:31	2026-08-15 13:26:31
128	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-15 13:54:57	2026-08-15 13:54:57
129	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-15 19:13:08	2026-08-15 19:13:08
130	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-15 23:17:01	2026-08-15 23:17:01
131	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-16 09:43:37	2026-08-16 09:43:37
132	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-16 10:39:03	2026-08-16 10:39:03
133	15	superadmin@cis.edu.ph	success	::1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-16 11:03:09	2026-08-16 11:03:09
134	\N	superadmin@cis.edu	failed	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-16 13:28:07	2026-08-16 13:28:07
135	\N	superadmin@cis.edu	failed	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-16 13:28:45	2026-08-16 13:28:45
136	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-16 13:29:39	2026-08-16 13:29:39
137	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-16 13:31:44	2026-08-16 13:31:44
138	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-16 13:32:01	2026-08-16 13:32:01
139	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-16 13:50:32	2026-08-16 13:50:32
140	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-16 19:41:56	2026-08-16 19:41:56
141	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-17 09:45:25	2026-08-17 09:45:25
142	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-17 14:42:48	2026-08-17 14:42:48
143	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-17 18:22:51	2026-08-17 18:22:51
144	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-17 19:43:25	2026-08-17 19:43:25
145	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-17 20:18:54	2026-08-17 20:18:54
146	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.133.0 Chrome/148.0.7778.280 Electron/42.8.0 Safari/537.36	2026-08-17 20:45:05	2026-08-17 20:45:05
147	56	trishamaemartinez8@gmail.com	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-18 09:59:51	2026-08-18 09:59:51
148	15	superadmin@cis.edu.ph	success	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-18 10:08:58	2026-08-18 10:08:58
149	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-18 10:11:18	2026-08-18 10:11:18
150	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-18 10:11:37	2026-08-18 10:11:37
151	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-18 10:12:05	2026-08-18 10:12:05
152	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-18 10:12:13	2026-08-18 10:12:13
153	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-18 10:15:35	2026-08-18 10:15:35
154	15	superadmin@cis.edu.ph	success	172.20.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	2026-08-18 10:20:02	2026-08-18 10:20:02
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_04_28_062251_create_students_table	1
5	2026_04_28_065500_create_guardians_table	1
6	2026_04_28_075708_create_teachers_table	1
7	2026_04_28_091044_create_enrollments_table	1
8	2026_04_28_122126_create_qr-codes_table	1
9	2026_04_30_084748_create_attendance_logs_table	1
10	2026_04_30_091100_create_schedule_configs_table	1
11	2026_04_30_092224_create_flagged_scans_table	1
12	2026_04_30_102631_create_email_logs_table	1
13	2026_05_08_124653_add_lrn_to_students_table	1
14	2026_05_08_131929_add_middle_name_to_students_table	1
15	2026_05_09_125225_create_personal_access_tokens_table	1
16	2026_05_10_073931_remove_fields_from_enrollments_table	1
17	2026_05_11_112800_add_level_to_enrollments_table	1
18	2026_06_01_181941_add_user_id_to_users_table	1
19	2026_06_01_201756_create_school_years_table	1
20	2026_06_01_212400_add_school_year_id_to_enrollments_table	1
21	2026_06_11_000000_add_unique_student_school_year_to_enrollments_table	1
22	2026_06_16_093708_create_sections_table	1
23	2026_06_16_180417_add_is_active_to_sections_table	1
24	2026_06_17_175023_add_section_id_to_enrollments_table	1
25	2026_06_19_204412_update_session_type_enum_on_enrollments_and_schedule_configs	1
26	2026_06_23_140014_add_capacity_to_sections_table	1
27	2026_06_28_171531_add_email_to_teachers_table	1
28	2026_06_28_172226_add_status_to_teachers_table	1
29	2026_06_28_172619_remove_email_from_teachers_table	1
30	2026_06_28_175402_remove_name_fields_from_teachers_table	1
31	2026_06_28_181112_rename_user_id_column_in_users_table	1
32	2026_07_02_203524_drop_school_year_from_enrollments_table	1
33	2026_07_04_130302_add_image_path_to_qr_codes_table	1
34	2026_07_12_201347_create_subjects_table	1
35	2026_07_12_201348_create_grading_periods_table	1
36	2026_07_12_201349_create_assessment_categories_table	1
37	2026_07_12_201350_create_teaching_assignments_table	1
46	2026_08_08_155007_drop_schedule_columns_from_teaching_assignments_table	2
47	2026_08_08_160018_drop_room_attendance_table	3
50	2026_08_08_161935_create_attendance_verifications_table	4
51	2026_08_08_164603_create_grading_configs_table	5
52	2026_08_08_172407_test_permissions_table	5
54	2026_08_08_174130_drop_session_type_from_teaching_assignments_table	7
55	2026_08_08_180259_update_session_type_check_constraint_on_enrollments_and_attendance_logs	8
56	2026_08_08_183021_update_flag_type_check_constraint_on_flagged_scans	9
57	2026_08_08_205933_final_test_table	9
59	2026_08_08_222013_add_period_type_to_grading_periods_table	11
60	2026_07_12_201351_create_room_attendance_table	12
61	2026_07_12_201352_create_assessments_table	12
62	2026_07_12_201353_create_student_assessment_scores_table	12
63	2026_07_12_201354_create_term_grades_table	12
64	2026_07_13_201540_create_login_logs_table	12
65	2026_07_29_000001_create_bulk_imports_table	12
66	2026_07_29_000002_create_bulk_import_issues_table	12
67	2026_07_29_000003_drop_section_from_enrollments_table	12
68	2026_08_08_180000_add_status_to_room_attendance_table	13
69	2026_08_09_161939_update_period_type_constraint_on_grading_periods	14
70	2026_08_10_173956_create_qr_attendances_table	15
71	2026_08_10_174624_create_attendance_verification_histories_table	16
72	2026_08_10_174758_create_system_settings_table	17
73	2026_08_10_175612_update_scan_type_constraint_on_attendance_logs	18
74	2026_08_10_180639_update_attendance_verifications_for_classroom_subjects	19
75	2026_08_10_192051_update_flag_type_constraint_on_flagged_scans_again	20
76	2026_08_10_192450_update_scan_type_constraint_on_email_logs	21
77	2026_08_12_000000_add_suffix_and_guardian_contact_to_student_forms_table	22
78	2026_08_12_000000_add_session_type_to_teaching_assignments_table	23
79	2026_08_12_000010_make_session_type_not_null_in_teaching_assignments	24
80	2026_08_12_000020_alter_session_type_to_not_null	25
81	2026_08_08_174435_create_missing_room_attendance_table	26
82	2026_08_14_000000_add_session_type_to_sections_table	27
83	2026_08_14_120000_drop_session_type_from_enrollments_and_teaching_assignments	28
84	2026_08_15_222744_add_sf1_fields_to_students_table	29
85	2026_08_15_223719_make_guardian_email_nullable	30
86	2026_08_15_224506_drop_birthdate_from_students_table	31
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: qr_attendances; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.qr_attendances (id, enrollment_id, attendance_date, time_in_log_id, time_out_log_id, spam_offense_count, cooldown_expires_at, created_at, updated_at) FROM stdin;
1	32	2026-08-12	2	8	5	2026-08-12 15:32:02	2026-08-12 11:37:34	2026-08-12 15:27:08
2	31	2026-08-12	10	\N	14	2026-08-12 22:46:44	2026-08-12 21:29:20	2026-08-12 22:43:31
3	32	2026-08-13	20	\N	3	2026-08-13 15:16:14	2026-08-13 13:51:15	2026-08-13 15:11:21
4	31	2026-08-13	25	\N	4	2026-08-13 16:34:43	2026-08-13 15:56:32	2026-08-13 16:29:46
5	33	2026-08-15	27	\N	1	2026-08-15 13:34:53	2026-08-15 13:30:33	2026-08-15 13:35:13
\.


--
-- Data for Name: qr_codes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.qr_codes (id, student_id, code, is_active, created_at, updated_at, image_path) FROM stdin;
2	35	EB62RVXT3GHBLYBHO0QX83GCZIQC438W	t	2026-08-10 00:02:08	2026-08-10 00:02:08	\N
3	36	ADT48P3LQBCGNNTWXXXQJP1Q6EO8CIEV	t	2026-08-10 00:43:18	2026-08-10 00:43:18	\N
4	38	DLJWEGJBEKTWLSORLYKHVLWABD9KBPOK	t	2026-08-10 00:50:27	2026-08-10 00:50:27	\N
5	39	0P1ZYXEPLSSZWKIFNGZZVQBYMQ9NSMOS	t	2026-08-10 01:17:10	2026-08-10 01:17:10	qr-codes/student-39.png
1	2	5Q9VOMUVFJBXHSJ2B8RPZ1ASIVZPOZHJ	t	2026-08-08 12:36:48	2026-08-12 11:27:09	qr-codes/student-2.png
6	40	ZYHLPRPJS8FB5PDOK7AGBHTPIYWITUA9	t	2026-08-12 11:29:57	2026-08-12 11:29:58	qr-codes/student-40.png
7	41	KZYMVK4X2ZWHTC9TLSLKYBOVERLGOSGDKNSS9WMHL3LONU5QWGSLNSOOVS5IRIBA	t	2026-08-12 16:52:16	2026-08-12 16:52:17	qr-codes/student-41.png
8	55	BTIJNCEH7WNOGX3Y2UHKVRQXZZPNZVCJOSTAK16ZTS0GUJPZEXAGJUJTZRJJJCUI	t	2026-08-14 13:08:20	2026-08-14 13:08:21	qr-codes/student-55.png
9	56	CTTDXKEZAIYRH3ILHRDL2XHUYD08WPRABWZGGQ2POS6CDKXT4LHDLY3D0WQGUVHT	t	2026-08-14 13:11:42	2026-08-14 13:11:42	qr-codes/student-56.png
10	57	FSDYW63WTTFTLKXJCFYRGUBU10LQX2NQOJBBEP20QUDVWMGCPTDL2LEXOOM18PER	t	2026-08-17 21:34:38	2026-08-17 21:34:39	qr-codes/student-57.png
11	58	SWOEZPO9VL6SNZCBQWTEAB0RRET6XVJVUYRCQPB5YWQRKGHDQE8509WEON6QX9PP	t	2026-08-17 21:35:03	2026-08-17 21:35:04	qr-codes/student-58.png
12	59	UZTELCCEFBAMVGMOIRZHWEVZBRJWEB0XDDUJSND7FVUGGVULMMMDU9HEUFLSB9FM	t	2026-08-17 21:35:21	2026-08-17 21:35:22	qr-codes/student-59.png
13	60	PBFWLRB22BZUJ40NSNCEJN2RHMONXTXVPXBMXM42SPGZ25EXACCCNRSUZ8GNY902	t	2026-08-17 21:35:43	2026-08-17 21:35:44	qr-codes/student-60.png
14	61	EML7LD1VND7TBINPJSCQ4LZVXI0ALORPPRWMBE0558TR0PUZHYBHQ7TSAEPCYJOA	t	2026-08-17 21:36:01	2026-08-17 21:36:02	qr-codes/student-61.png
15	62	NREHG0S7QQ06VYYNDEYZRM6BMJAIY8LX02OQC89GFHGDIREB1WZ81UGYFAVMC3IX	t	2026-08-17 21:36:14	2026-08-17 21:36:16	qr-codes/student-62.png
16	63	XJXHSWLQHH5Y2E259TY78HAAX7OXSKM6JLT6XOPBMP0T7AQKSXFHS6NMGPSJTTVW	t	2026-08-17 21:36:28	2026-08-17 21:36:30	qr-codes/student-63.png
17	64	H4DTVXAAPQMLLPFXBGYXH4W9T9RSU410NF4VLK0Q2NVRSHQ7FG0DQNVNK5FDF5GN	t	2026-08-17 21:36:39	2026-08-17 21:36:40	qr-codes/student-64.png
18	65	0CN6HM7FMXR8FZDY4OJAOJDCDW8UITRJNOHUR1AM0Z4F8AB7T5TK2KGDS3IRW2RM	t	2026-08-17 21:36:51	2026-08-17 21:36:51	qr-codes/student-65.png
\.


--
-- Data for Name: term_grades; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.term_grades (id, teaching_assignment_id, enrollment_id, grading_period_id, written_work_grade, performance_task_grade, term_assessment_grade, initial_grade, transmuted_grade, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: room_attendance; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.room_attendance (id, teaching_assignment_id, enrollment_id, attendance_date, time_in, time_out, remarks, created_at) FROM stdin;
\.


--
-- Data for Name: schedule_configs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.schedule_configs (id, level, session_type, in_start, in_end, late_threshold, out_start, out_end, created_at, updated_at) FROM stdin;
2	hs	morning	05:00:00	10:00:00	08:39:00	10:10:00	17:00:00	2026-08-13 13:41:22	2026-08-13 13:41:22
3	hs	afternoon	05:00:00	16:00:00	14:00:00	16:20:00	20:00:00	2026-08-13 13:42:12	2026-08-13 13:42:12
4	shs	morning	05:00:00	10:00:00	08:00:00	11:10:00	17:00:00	2026-08-13 13:42:59	2026-08-13 13:42:59
5	shs	afternoon	07:00:00	16:00:00	14:00:00	16:10:00	20:00:00	2026-08-13 13:43:48	2026-08-13 13:43:48
1	elementary	whole_day	05:00:00	16:00:00	08:30:00	16:30:00	23:59:00	2026-08-12 11:25:23	2026-08-13 13:50:27
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
jcMIll6bj0iwjcFC84EU1LXScDARS1ewNVSAk62D	15	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJ0ZGZoaFp2dlAyUHAwdXl6Y0pXbDZLNHluT2M2bm11RXhTRGhZVzRaIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2ltcG9ydFwvaGlzdG9yeVwvbGlzdD9wYWdlPTEmcGVyX3BhZ2U9MTUiLCJyb3V0ZSI6ImltcG9ydC5oaXN0b3J5In0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxNX0=	1787019577
\.


--
-- Data for Name: student_assessment_scores; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.student_assessment_scores (id, assessment_id, enrollment_id, score, remarks, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: system_settings; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.system_settings (id, key, value, description, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: schema_migrations; Type: TABLE DATA; Schema: realtime; Owner: -
--

COPY realtime.schema_migrations (version, inserted_at) FROM stdin;
20211116024918	2026-08-02 06:18:49
20211116045059	2026-08-02 06:18:49
20211116050929	2026-08-02 06:18:49
20211116051442	2026-08-02 06:18:49
20211116212300	2026-08-02 06:18:49
20211116213355	2026-08-02 06:18:49
20211116213934	2026-08-02 06:18:49
20211116214523	2026-08-02 06:18:49
20211122062447	2026-08-02 06:18:49
20211124070109	2026-08-02 06:18:49
20211202204204	2026-08-02 06:18:49
20211202204605	2026-08-02 06:18:49
20211210212804	2026-08-02 06:18:49
20211228014915	2026-08-02 06:18:49
20220107221237	2026-08-02 06:18:49
20220228202821	2026-08-02 06:18:49
20220312004840	2026-08-02 06:18:49
20220603231003	2026-08-02 06:18:49
20220603232444	2026-08-02 06:18:49
20220615214548	2026-08-02 06:18:49
20220712093339	2026-08-02 06:18:49
20220908172859	2026-08-02 06:18:49
20220916233421	2026-08-02 06:18:49
20230119133233	2026-08-02 06:18:49
20230128025114	2026-08-02 06:18:49
20230128025212	2026-08-02 06:18:49
20230227211149	2026-08-02 06:18:49
20230228184745	2026-08-02 06:18:49
20230308225145	2026-08-02 06:18:49
20230328144023	2026-08-02 06:18:49
20231018144023	2026-08-02 06:18:49
20231204144023	2026-08-02 06:18:49
20231204144024	2026-08-02 06:18:49
20231204144025	2026-08-02 06:18:49
20240108234812	2026-08-02 06:18:49
20240109165339	2026-08-02 06:18:49
20240227174441	2026-08-02 06:18:49
20240311171622	2026-08-02 06:18:49
20240321100241	2026-08-02 06:18:49
20240401105812	2026-08-02 06:18:49
20240418121054	2026-08-02 06:18:49
20240523004032	2026-08-02 06:18:49
20240618124746	2026-08-02 06:18:49
20240801235015	2026-08-02 06:18:49
20240805133720	2026-08-02 06:18:49
20240827160934	2026-08-02 06:18:49
20240919163303	2026-08-02 06:18:49
20240919163305	2026-08-02 06:18:49
20241019105805	2026-08-02 06:18:49
20241030150047	2026-08-02 06:18:49
20241108114728	2026-08-02 06:18:49
20241121104152	2026-08-02 06:18:49
20241130184212	2026-08-02 06:18:49
20241220035512	2026-08-02 06:18:49
20241220123912	2026-08-02 06:18:49
20241224161212	2026-08-02 06:18:49
20250107150512	2026-08-02 06:18:49
20250110162412	2026-08-02 06:18:49
20250123174212	2026-08-02 06:18:49
20250128220012	2026-08-02 06:18:49
20250506224012	2026-08-02 06:18:49
20250523164012	2026-08-02 06:18:49
20250714121412	2026-08-02 06:18:49
20250905041441	2026-08-02 06:18:49
20251103001201	2026-08-02 06:18:49
20251120212548	2026-08-02 06:18:49
20251120215549	2026-08-02 06:18:49
20260218120000	2026-08-02 06:18:49
20260326120000	2026-08-02 06:18:49
20260514120000	2026-08-02 06:18:49
20260527120000	2026-08-02 06:18:49
20260528120000	2026-08-02 06:18:49
20260603120000	2026-08-02 06:18:49
20260605120000	2026-08-02 06:18:49
20260606110000	2026-08-02 06:18:49
20260616120000	2026-08-02 06:18:49
20260624120000	2026-08-02 06:18:49
20260626120000	2026-08-02 06:18:49
20260706120000	2026-08-02 06:18:49
20260707120000	2026-08-02 06:18:49
20260709120000	2026-08-02 06:18:49
\.


--
-- Data for Name: subscription; Type: TABLE DATA; Schema: realtime; Owner: -
--

COPY realtime.subscription (id, subscription_id, entity, filters, claims, created_at, action_filter, selected_columns) FROM stdin;
\.


--
-- Data for Name: buckets; Type: TABLE DATA; Schema: storage; Owner: -
--

COPY storage.buckets (id, name, owner, created_at, updated_at, public, avif_autodetection, file_size_limit, allowed_mime_types, owner_id, type) FROM stdin;
\.


--
-- Data for Name: buckets_analytics; Type: TABLE DATA; Schema: storage; Owner: -
--

COPY storage.buckets_analytics (name, type, format, created_at, updated_at, id, deleted_at) FROM stdin;
\.


--
-- Data for Name: buckets_vectors; Type: TABLE DATA; Schema: storage; Owner: -
--

COPY storage.buckets_vectors (id, type, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: storage; Owner: -
--

COPY storage.migrations (id, name, hash, executed_at) FROM stdin;
0	create-migrations-table	e18db593bcde2aca2a408c4d1100f6abba2195df	2026-08-02 06:19:14.656059
1	initialmigration	6ab16121fbaa08bbd11b712d05f358f9b555d777	2026-08-02 06:19:14.702758
2	storage-schema	f6a1fa2c93cbcd16d4e487b362e45fca157a8dbd	2026-08-02 06:19:14.709346
3	pathtoken-column	2cb1b0004b817b29d5b0a971af16bafeede4b70d	2026-08-02 06:19:14.740019
4	add-migrations-rls	427c5b63fe1c5937495d9c635c263ee7a5905058	2026-08-02 06:19:14.765221
5	add-size-functions	79e081a1455b63666c1294a440f8ad4b1e6a7f84	2026-08-02 06:19:14.771362
6	change-column-name-in-get-size	ded78e2f1b5d7e616117897e6443a925965b30d2	2026-08-02 06:19:14.778368
7	add-rls-to-buckets	e7e7f86adbc51049f341dfe8d30256c1abca17aa	2026-08-02 06:19:14.78438
8	add-public-to-buckets	fd670db39ed65f9d08b01db09d6202503ca2bab3	2026-08-02 06:19:14.790404
9	fix-search-function	af597a1b590c70519b464a4ab3be54490712796b	2026-08-02 06:19:14.798533
10	search-files-search-function	b595f05e92f7e91211af1bbfe9c6a13bb3391e16	2026-08-02 06:19:14.804376
11	add-trigger-to-auto-update-updated_at-column	7425bdb14366d1739fa8a18c83100636d74dcaa2	2026-08-02 06:19:14.81025
12	add-automatic-avif-detection-flag	8e92e1266eb29518b6a4c5313ab8f29dd0d08df9	2026-08-02 06:19:14.816979
13	add-bucket-custom-limits	cce962054138135cd9a8c4bcd531598684b25e7d	2026-08-02 06:19:14.822967
14	use-bytes-for-max-size	941c41b346f9802b411f06f30e972ad4744dad27	2026-08-02 06:19:14.828769
15	add-can-insert-object-function	934146bc38ead475f4ef4b555c524ee5d66799e5	2026-08-02 06:19:14.8625
16	add-version	76debf38d3fd07dcfc747ca49096457d95b1221b	2026-08-02 06:19:14.868844
17	drop-owner-foreign-key	f1cbb288f1b7a4c1eb8c38504b80ae2a0153d101	2026-08-02 06:19:14.874214
18	add_owner_id_column_deprecate_owner	e7a511b379110b08e2f214be852c35414749fe66	2026-08-02 06:19:14.87952
19	alter-default-value-objects-id	02e5e22a78626187e00d173dc45f58fa66a4f043	2026-08-02 06:19:14.888349
20	list-objects-with-delimiter	cd694ae708e51ba82bf012bba00caf4f3b6393b7	2026-08-02 06:19:14.895629
21	s3-multipart-uploads	8c804d4a566c40cd1e4cc5b3725a664a9303657f	2026-08-02 06:19:14.903386
22	s3-multipart-uploads-big-ints	9737dc258d2397953c9953d9b86920b8be0cdb73	2026-08-02 06:19:14.919888
23	optimize-search-function	9d7e604cddc4b56a5422dc68c9313f4a1b6f132c	2026-08-02 06:19:14.932569
24	operation-function	8312e37c2bf9e76bbe841aa5fda889206d2bf8aa	2026-08-02 06:19:14.939343
25	custom-metadata	d974c6057c3db1c1f847afa0e291e6165693b990	2026-08-02 06:19:14.944816
26	objects-prefixes	215cabcb7f78121892a5a2037a09fedf9a1ae322	2026-08-02 06:19:14.950643
27	search-v2	859ba38092ac96eb3964d83bf53ccc0b141663a6	2026-08-02 06:19:14.955631
28	object-bucket-name-sorting	c73a2b5b5d4041e39705814fd3a1b95502d38ce4	2026-08-02 06:19:14.96133
29	create-prefixes	ad2c1207f76703d11a9f9007f821620017a66c21	2026-08-02 06:19:14.966803
30	update-object-levels	2be814ff05c8252fdfdc7cfb4b7f5c7e17f0bed6	2026-08-02 06:19:14.971876
31	objects-level-index	b40367c14c3440ec75f19bbce2d71e914ddd3da0	2026-08-02 06:19:14.977513
32	backward-compatible-index-on-objects	e0c37182b0f7aee3efd823298fb3c76f1042c0f7	2026-08-02 06:19:14.982589
33	backward-compatible-index-on-prefixes	b480e99ed951e0900f033ec4eb34b5bdcb4e3d49	2026-08-02 06:19:14.987554
34	optimize-search-function-v1	ca80a3dc7bfef894df17108785ce29a7fc8ee456	2026-08-02 06:19:14.992497
35	add-insert-trigger-prefixes	458fe0ffd07ec53f5e3ce9df51bfdf4861929ccc	2026-08-02 06:19:14.997392
36	optimise-existing-functions	6ae5fca6af5c55abe95369cd4f93985d1814ca8f	2026-08-02 06:19:15.002634
37	add-bucket-name-length-trigger	3944135b4e3e8b22d6d4cbb568fe3b0b51df15c1	2026-08-02 06:19:15.007626
38	iceberg-catalog-flag-on-buckets	02716b81ceec9705aed84aa1501657095b32e5c5	2026-08-02 06:19:15.013511
39	add-search-v2-sort-support	6706c5f2928846abee18461279799ad12b279b78	2026-08-02 06:19:15.027022
40	fix-prefix-race-conditions-optimized	7ad69982ae2d372b21f48fc4829ae9752c518f6b	2026-08-02 06:19:15.031809
41	add-object-level-update-trigger	07fcf1a22165849b7a029deed059ffcde08d1ae0	2026-08-02 06:19:15.037446
42	rollback-prefix-triggers	771479077764adc09e2ea2043eb627503c034cd4	2026-08-02 06:19:15.043129
43	fix-object-level	84b35d6caca9d937478ad8a797491f38b8c2979f	2026-08-02 06:19:15.048386
44	vector-bucket-type	99c20c0ffd52bb1ff1f32fb992f3b351e3ef8fb3	2026-08-02 06:19:15.053235
45	vector-buckets	049e27196d77a7cb76497a85afae669d8b230953	2026-08-02 06:19:15.059952
46	buckets-objects-grants	fedeb96d60fefd8e02ab3ded9fbde05632f84aed	2026-08-02 06:19:15.076944
47	iceberg-table-metadata	649df56855c24d8b36dd4cc1aeb8251aa9ad42c2	2026-08-02 06:19:15.083178
48	iceberg-catalog-ids	e0e8b460c609b9999ccd0df9ad14294613eed939	2026-08-02 06:19:15.089736
49	buckets-objects-grants-postgres	072b1195d0d5a2f888af6b2302a1938dd94b8b3d	2026-08-02 06:19:15.113023
50	search-v2-optimised	6323ac4f850aa14e7387eb32102869578b5bd478	2026-08-02 06:19:15.120795
51	index-backward-compatible-search	2ee395d433f76e38bcd3856debaf6e0e5b674011	2026-08-02 06:19:16.059677
52	drop-not-used-indexes-and-functions	5cc44c8696749ac11dd0dc37f2a3802075f3a171	2026-08-02 06:19:16.061432
53	drop-index-lower-name	d0cb18777d9e2a98ebe0bc5cc7a42e57ebe41854	2026-08-02 06:19:16.073399
54	drop-index-object-level	6289e048b1472da17c31a7eba1ded625a6457e67	2026-08-02 06:19:16.076573
55	prevent-direct-deletes	262a4798d5e0f2e7c8970232e03ce8be695d5819	2026-08-02 06:19:16.078244
56	fix-optimized-search-function	b823ed1e418101032fa01374edc9a436e54e3ed4	2026-08-02 06:19:16.08662
57	s3-multipart-uploads-metadata	f127886e00d1b374fadbc7c6b31e09336aad5287	2026-08-02 06:19:16.09472
58	operation-ergonomics	00ca5d483b3fe0d522133d9002ccc5df98365120	2026-08-02 06:19:16.101242
59	drop-unused-functions	38456f13e39691c2bbb4b5151d0d1cdbabd4a8c4	2026-08-02 06:19:16.107626
60	optimize-existing-functions-again	db35e1c91a9201e59f4fef8d972c2f277d68b157	2026-08-02 06:19:16.113385
61	mark-filename-immutable	fe0096517ae9d60aaec1d110172ba9036dc66bb7	2026-08-12 02:11:34.381254
\.


--
-- Data for Name: objects; Type: TABLE DATA; Schema: storage; Owner: -
--

COPY storage.objects (id, bucket_id, name, owner, created_at, updated_at, last_accessed_at, metadata, version, owner_id, user_metadata) FROM stdin;
\.


--
-- Data for Name: s3_multipart_uploads; Type: TABLE DATA; Schema: storage; Owner: -
--

COPY storage.s3_multipart_uploads (id, in_progress_size, upload_signature, bucket_id, key, version, owner_id, created_at, user_metadata, metadata) FROM stdin;
\.


--
-- Data for Name: s3_multipart_uploads_parts; Type: TABLE DATA; Schema: storage; Owner: -
--

COPY storage.s3_multipart_uploads_parts (id, upload_id, size, part_number, bucket_id, key, etag, owner_id, version, created_at) FROM stdin;
\.


--
-- Data for Name: vector_indexes; Type: TABLE DATA; Schema: storage; Owner: -
--

COPY storage.vector_indexes (id, name, bucket_id, data_type, dimension, distance_metric, metadata_configuration, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: secrets; Type: TABLE DATA; Schema: vault; Owner: -
--

COPY vault.secrets (id, name, description, secret, key_id, nonce, created_at, updated_at) FROM stdin;
\.


--
-- Name: refresh_tokens_id_seq; Type: SEQUENCE SET; Schema: auth; Owner: -
--

SELECT pg_catalog.setval('auth.refresh_tokens_id_seq', 1, false);


--
-- Name: assessment_categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.assessment_categories_id_seq', 3, true);


--
-- Name: assessments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.assessments_id_seq', 2, true);


--
-- Name: attendance_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attendance_logs_id_seq', 28, true);


--
-- Name: attendance_verification_histories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attendance_verification_histories_id_seq', 1, false);


--
-- Name: attendance_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attendance_verifications_id_seq', 3, true);


--
-- Name: bulk_import_issues_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.bulk_import_issues_id_seq', 21, true);


--
-- Name: bulk_imports_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.bulk_imports_id_seq', 4, true);


--
-- Name: email_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.email_logs_id_seq', 6, true);


--
-- Name: enrollments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.enrollments_id_seq', 45, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: flagged_scans_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.flagged_scans_id_seq', 41, true);


--
-- Name: grading_configs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grading_configs_id_seq', 1, false);


--
-- Name: grading_periods_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grading_periods_id_seq', 4, true);


--
-- Name: guardians_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.guardians_id_seq', 28, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: login_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.login_logs_id_seq', 154, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 86, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: qr_attendances_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.qr_attendances_id_seq', 5, true);


--
-- Name: qr_codes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.qr_codes_id_seq', 18, true);


--
-- Name: term_grades_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.term_grades_id_seq', 1, true);


--
-- Name: room_attendance_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.room_attendance_id_seq', 1, false);


--
-- Name: schedule_configs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.schedule_configs_id_seq', 5, true);


--
-- Name: school_years_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.school_years_id_seq', 8, true);


--
-- Name: sections_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.sections_id_seq', 7, true);


--
-- Name: student_assessment_scores_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.student_assessment_scores_id_seq', 2, true);


--
-- Name: students_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.students_id_seq', 65, true);


--
-- Name: subjects_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.subjects_id_seq', 2, true);


--
-- Name: system_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.system_settings_id_seq', 1, false);


--
-- Name: teachers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.teachers_id_seq', 14, true);


--
-- Name: teaching_assignments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.teaching_assignments_id_seq', 5, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 78, true);


--
-- Name: subscription_id_seq; Type: SEQUENCE SET; Schema: realtime; Owner: -
--

SELECT pg_catalog.setval('realtime.subscription_id_seq', 1, false);


--
-- PostgreSQL database dump complete
--

\unrestrict LWiKWRUSrZVoQcN6G04GQWFvOM0xncCiA9jdHJYlwOXpRmZYgl8Ir7HaMCztJdS

