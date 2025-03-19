# Assignment 1 API Docs

Base URL: `https://w23003084.nuwebspace.co.uk/as1/`

## Authentication

-   All endpoints require an API key in the `X-API-KEY` header.
-   Example: `X-API-KEY: w23003084apikey`

## Developer Information

-   Endpoint: `GET https://w23003084.nuwebspace.co.uk/as1/api/developer`
-   Description: Retrieves developer information including student ID and name.
-   Response:

    ```json
    {
    "student_id": "W23003084",
    "name": "Will Hick"
    }
    ```

## Authors

-   Endpoint: `GET https://w23003084.nuwebspace.co.uk/as1/api/author`
-   Description: Retrieves a list of authors.
-   Parameters:
    -   `author_id` (optional): Integer. Retrieves a specific author by ID.
    -   `content_id` (optional): Integer. Retrieves authors associated with a specific content ID.
    -   `search` (optional): String. Searches authors by name.
    -   `page` (optional): Integer. Specifies the page number for pagination (10 items per page).
-   Response:

    ```json
    [
    {
    "author_id": 1,
    "name": "Author Name"
    }
    ]
    ```

## Content

-   Endpoint: `GET https://w23003084.nuwebspace.co.uk/as1/api/content`
-   Description: Retrieves content items.
-   Parameters:
    -   `content_id` (optional): Integer. Retrieves a specific content item by ID.
    -   `author_id` (optional): Integer. Retrieves content items associated with a specific author ID.
    -   `search` (optional): String. Searches content items by title or abstract.
    -   `page` (optional): Integer. Specifies the page number for pagination (10 items per page).
-   Response:

    ```json
    [
    {
    "content_id": 1,
    "title": "Content Title",
    "abstract": "Content abstract...",
    "doi_link": "doi link",
    "preview_video": "link to video",
    "type": "Type",
    "award": "Award"
    }
    ]
    ```

## Awards

-   Endpoint: `GET https://w23003084.nuwebspace.co.uk/as1/api/award`
-   Description: Retrieves a list of awards.
-   Response:

    ```json
    [
    {
    "award_id": 1,
    "name": "Award Name"
    }
    ]
    ```
-   Endpoint: `POST https://w23003084.nuwebspace.co.uk/as1/api/award`
-   Description: Creates a new award.
-   Body:

    ```json
    {
    "name": "Award Name"
    }
    ```
-   Endpoint: `PATCH https://w23003084.nuwebspace.co.uk/as1/api/award`
-   Description: Updates an existing award.
-   Body:

    ```json
    {
    "award_id": 1,
    "name": "New Award Name"
    }
    ```
-   Endpoint: `DELETE https://w23003084.nuwebspace.co.uk/as1/api/award`
-   Description: Deletes an award.
-   Body:

    ```json
    {
    "award_id": 1
    }
    ```

## Manage Awards

-   Endpoint: `POST https://w23003084.nuwebspace.co.uk/as1/api/manage_awards`
-   Description: Assigns an award to a content item.
-   Body:

    ```json
    {
    "content_id": 1,
    "award_id": 1
    }
    ```
-   Endpoint: `DELETE https://w23003084.nuwebspace.co.uk/as1/api/manage_awards`
-   Description: Removes an award from a content item.
-   Body:

    ```json
    {
    "content_id": 1
    }
    ```


