import React from 'react';
import { Link } from '@inertiajs/react';

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Props {
  links: PaginationLink[];
}

const Pagination: React.FC<Props> = ({ links }) => {
  if (!links || links.length === 0) return null;

  return (
    <nav className="flex items-center justify-between">
      <div className="flex justify-between flex-1 sm:hidden">
        {links[0]?.url && (
          <Link
            href={links[0].url}
            className="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Anterior
          </Link>
        )}
        {links[links.length - 1]?.url && (
          <Link
            href={links[links.length - 1].url}
            className="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Próximo
          </Link>
        )}
      </div>
      <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
          <nav className="relative z-0 inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
            {links.map((link, index) => {
              if (link.label === '&laquo; Previous') {
                return (
                  <Link
                    key={index}
                    href={link.url || '#'}
                    className={`relative inline-flex items-center px-2 py-2 text-sm font-medium rounded-l-md border ${
                      link.url
                        ? 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50'
                        : 'text-gray-300 bg-gray-100 border-gray-300 cursor-default'
                    }`}
                  >
                    <span className="sr-only">Anterior</span>
                    <svg className="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clipRule="evenodd" />
                    </svg>
                  </Link>
                );
              }

              if (link.label === 'Next &raquo;') {
                return (
                  <Link
                    key={index}
                    href={link.url || '#'}
                    className={`relative inline-flex items-center px-2 py-2 text-sm font-medium rounded-r-md border ${
                      link.url
                        ? 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50'
                        : 'text-gray-300 bg-gray-100 border-gray-300 cursor-default'
                    }`}
                  >
                    <span className="sr-only">Próximo</span>
                    <svg className="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                    </svg>
                  </Link>
                );
              }

              return (
                <Link
                  key={index}
                  href={link.url || '#'}
                  className={`relative inline-flex items-center px-4 py-2 text-sm font-medium border ${
                    link.active
                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                      : link.url
                      ? 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                      : 'bg-gray-100 border-gray-300 text-gray-300 cursor-default'
                  }`}
                >
                  {link.label}
                </Link>
              );
            })}
          </nav>
        </div>
      </div>
    </nav>
  );
};

export default Pagination; 