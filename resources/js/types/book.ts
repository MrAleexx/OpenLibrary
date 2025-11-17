export interface Contributor {
    full_name: string;
    contributor_type:
        | 'author'
        | 'editor'
        | 'translator'
        | 'illustrator'
        | 'prologuist';
    sequence_number: number;
}

export interface BookDetails {
    description?: string;
    edition?: string;
    keywords?: string;
}

export interface Book {
    id: number;
    title: string;
    publisher_id: number | null;
    isbn: string;
    language_code: string;
    pages: number | null;
    publication_year: number | null;
    book_type: 'digital' | 'physical' | 'both';
    featured: boolean;
    is_active: boolean;
    downloadable: boolean;
    cover_image: string | null;
    pdf_file: string | null;
    details?: BookDetails;
    categories?: Array<{ id: number; name: string }>;
    contributors?: Contributor[];
}

export interface BookFormData {
    title: string;
    publisher_id: number | null;
    isbn: string;
    language_code: string;
    pages: number | string;
    publication_year: number | string;
    book_type: string;
    featured: boolean;
    is_active: boolean;
    downloadable: boolean;
    description: string;
    edition: string;
    keywords: string;
    categories: string[];
    contributors: Contributor[];
    cover_image: File | null;
    pdf_file: File | null;
}
